<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Migrations;

use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use function app;

class BraintreeMigration extends Migration
{
    protected Gateway   $gateway;

    public function __construct(
        Subscription $previous,
        User $user,
        string $paymentMethodId,
        bool $useSetupIntent = true
    )
    {
        parent::__construct($previous, $user, $paymentMethodId, $useSetupIntent);

        $this->gateway = app()->make(Gateway::class);
    }

    /**
     * @return $this|Migration
     *
     * @throws NotFound
     * @throws ApiErrorException
     */
    public function run(): self
    {
        /** @var BraintreeSubscription $btSubscription */
        $btSubscription = $this->gateway->subscription()->find($this->previous->id);

        $this->stripeSubscription = $this->client->subscriptions->create([
            'customer'               => $this->customer->id,
            'items'                  => [
                ['price' => $this->prices->get($btSubscription->planId)->id],
            ],
            'default_payment_method' => $this->paymentMethodId,
            'billing_cycle_anchor'   => $btSubscription->billingPeriodEndDate->getTimestamp(),
            'prorate'                => false,
            'metadata'               => $this->getMetadata(),
        ]);

        return $this;
    }

    public function finalize(): self
    {
        $this->subscription = $this->dispatcher->dispatchNow(new UpsertSubscription($this->stripeSubscription));

        // Cancel the previous subscription
        $this->dispatcher->dispatchNow(new UpsertSubscription(
            $this->gateway->subscription()->cancel($this->previous->id)->subscription,
        ));

        $this->user->flushCache();

        return $this;
    }
}
