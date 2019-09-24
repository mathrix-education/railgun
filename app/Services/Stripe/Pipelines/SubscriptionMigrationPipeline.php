<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Services\Braintree\Exceptions\InvalidBraintreeCustomer;
use Braintree\Exception\NotFound;
use Braintree\Subscription as BraintreeSubscription;
use Carbon\Carbon;
use Mathrix\Lumen\Zero\Exceptions\Http\Http401UnauthorizedException;
use Stripe\SetupIntent as StripeSetupIntent;
use Stripe\Subscription as StripeSubscription;
use function gateway;

/**
 * Migrate Braintree Subscriptions to Stripe.
 */
class SubscriptionMigrationPipeline extends Pipeline
{
    private const PLANS_EQUIVALENCE = [
        'v4_monthly' => 'v5_monthly',
        'v4_annually' => 'v5_annually',
    ];
    /** @var StripeSubscription $subscription The new Stripe subscription. */
    public $subscription;
    /** @var string $braintreeSubscriptionId The Braintree subscription id. */
    private $braintreeSubscriptionId;
    /** @var BraintreeSubscription $braintreeSubscription The Braintree subscription. */
    private $braintreeSubscription;

    /**
     * @param string $braintreeSubscriptionId The Braintree subscription id to migrate.
     */
    public function __construct(string $braintreeSubscriptionId)
    {
        parent::__construct();
        $this->braintreeSubscriptionId = $braintreeSubscriptionId;
    }

    /**
     * Check that the user is a valid Braintree customer.
     *
     * @throws InvalidBraintreeCustomer
     */
    protected function initialize(): void
    {
        if (!$this->user->isBraintreeCustomer()) {
            throw new InvalidBraintreeCustomer($this->user);
        }

        if ($this->user->isStripeCustomer()) {
            return;
        }

        (new CustomerCreationPipeline())->flow();
    }

    /**
     * Retrieve the Braintree subscription, and setup a new Stripe Subscription using the billing_anchor feature.
     *
     * @throws Http401UnauthorizedException
     * @throws NotFound
     */
    protected function exchange(): void
    {
        $this->braintreeSubscription = gateway()->subscription()->find($this->braintreeSubscriptionId);

        if (! empty($this->braintreeSubscription->transactions)) {
            // There is a least one subscription.
            $transaction = $this->braintreeSubscription->transactions[0];

            if ($transaction->customerDetails->id !== $this->user->braintree_id) {
                throw new Http401UnauthorizedException();
            }
        }

        /**
         * @var Carbon $prorationEndsAt The proration end date, which is when the first payment will be triggered.
         */
        $prorationEndsAt = Carbon::instance($this->braintreeSubscription->nextBillingDate);

        $payload = [
            'customer' => $this->user->stripe_id,
            'billing_cycle_anchor' => $prorationEndsAt->getTimestamp(),
            'default_source' => $this->request->json('card.id') ?? $this->user->asStripeCustomer()->default_source,
            'items' => [
                ['plan' => self::PLANS_EQUIVALENCE[$this->braintreeSubscription->planId]],
            ],
            'metadata' => [
                'user_id' => $this->user->id,
            ],
            'prorate' => false, // Very important, since the customer should have already paid for the subscription
            'expand' => [
                'latest_invoice.payment_intent',
                'pending_setup_intent',
            ],
        ];

        $this->subscription = StripeSubscription::create($payload);
    }

    /**
     * If the Stripe subscription is active, cancel the Braintree subscription immediately.
     * If the Stripe subscription requires a 3D Secure 2 confirmation, write the Braintree subscription id to the Stripe
     * setup intent metadata in order to cancel it when receiving the webhook.
     */
    protected function finalize(): void
    {
        if ($this->subscription->pending_setup_intent === null) {
            // New subscription has an active subscription, we can cancel the Braintree one.
            gateway()->subscription()->cancel($this->braintreeSubscription->id);
        } else {
            /*
             * New subscription require 3D Secure confirmation, write the Braintree subscription id to the setup intent
             * metadata in order to cancel it when receiving the webhook.
             */
            /** @var StripeSetupIntent $setupIntent */
            $setupIntent = $this->subscription->pending_setup_intent;
            StripeSetupIntent::update($setupIntent->id, [
                'metadata' => [
                    'braintree_subscription_id' => $this->braintreeSubscriptionId,
                ],
            ]);
        }

        $this->user->invalidate();
    }
}
