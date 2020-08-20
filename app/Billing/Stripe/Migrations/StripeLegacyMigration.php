<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Migrations;

use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use function app;

class StripeLegacyMigration extends Migration
{
    protected StripeClient $clientLegacy;

    public function __construct(
        Subscription $previous,
        User $user,
        string $paymentMethodId,
        bool $useSetupIntent = true
    )
    {
        parent::__construct($previous, $user, $paymentMethodId, $useSetupIntent);

        $this->clientLegacy = app()->make(StripeClientRegistry::class)->make(StripeClientRegistry::CLIENT_LEGACY);
    }

    /**
     * @return $this
     *
     * @throws ApiErrorException
     */
    public function run(): self
    {
        $stripeSubscription = $this->clientLegacy->subscriptions->retrieve($this->previous->id);

        $this->stripeSubscription = $this->client->subscriptions->create([
            'customer'               => $this->customer->id,
            'items'                  => [
                ['price' => $this->modeMachine->get($stripeSubscription->plan->id)->id],
            ],
            'default_payment_method' => $this->paymentMethodId,
            'billing_cycle_anchor'   => $stripeSubscription->current_period_end,
            'prorate'                => false,
            'metadata'               => $this->getMetadata(),
        ]);

        return $this;
    }

    /**
     * @return $this
     *
     * @throws ApiErrorException
     */
    public function finalize(): self
    {
        $this->subscription = $this->dispatcher->dispatchNow(new UpsertSubscription($this->stripeSubscription));

        // Cancel the previous subscription
        $this->dispatcher->dispatchNow(new UpsertSubscription(
            $this->clientLegacy->subscriptions->cancel($this->previous->id, ['prorate' => false]),
        ));

        $this->user->flushCache();

        return $this;
    }
}
