<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Migrations;

use Illuminate\Bus\Dispatcher;
use Railgun\Billing\Stripe\Exceptions\ThreeDSecureException;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\SetupIntent;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use function app;

abstract class Migration
{
    protected Dispatcher   $dispatcher;
    protected StripeClient $client;
    protected ModeMachine  $modeMachine;

    protected Subscription $previous;
    protected User         $user;
    protected string       $paymentMethodId;
    protected bool         $useSetupIntent;

    protected Customer           $customer;
    protected StripeSubscription $stripeSubscription;
    protected Subscription       $subscription;

    public function __construct(
        Subscription $previous,
        User $user,
        string $paymentMethodId,
        bool $useSetupIntent = true
    )
    {
        $this->dispatcher  = app()->make(Dispatcher::class);
        $this->client      = app()->make(StripeClient::class);
        $this->modeMachine = app()->make(ModeMachine::class);

        $this->previous        = $previous;
        $this->user            = $user;
        $this->paymentMethodId = $paymentMethodId;
        $this->useSetupIntent  = $useSetupIntent;
    }

    /**
     * @return mixed[] The migration metadata.
     */
    protected function getMetadata(): array
    {
        return [
            'previous_processor' => $this->previous->processor,
            'previous_id'        => $this->previous->id,
        ];
    }

    /**
     * Setup the checkout (in this can, ensure that the user is a stripe customer).
     *
     * @return $this
     *
     * @throws ApiErrorException
     */
    public function setup(): self
    {
        $this->customer = $this->user->ensureStripeCustomer();

        if ($this->useSetupIntent) {
            $setupIntent = $this->client->setupIntents->create([
                'confirm'        => true,
                'customer'       => $this->customer->id,
                'payment_method' => $this->paymentMethodId,
                'usage'          => 'off_session',
                'metadata'       => $this->getMetadata(),
            ]);

            if ($setupIntent->status === SetupIntent::STATUS_REQUIRES_ACTION) {
                $this->client->setupIntents->update($setupIntent->id, [
                    'metadata' => [
                        'next_action' => 'migrate',
                    ],
                ]);

                throw new ThreeDSecureException($setupIntent);
            }

            $this->paymentMethodId = (string)$setupIntent->payment_method;
        }

        return $this;
    }

    /**
     * Validate the checkout parameters.
     *
     * @return $this
     */
    public function validate(): self
    {
        return $this;
    }

    /**
     * Execute the subscription migration.
     *
     * @return $this
     */
    abstract public function run(): self;

    /**
     * After a successful invoice build, execute the callbacks to deliver products to the recipient.
     *
     * @return $this
     */
    abstract public function finalize(): self;

    /**
     * Run the checkout pipeline.
     *
     * @return $this
     *
     * @throws ApiErrorException
     */
    public function execute(): self
    {
        return $this->setup()
            ->validate()
            ->run()
            ->finalize();
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
