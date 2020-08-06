<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Subscription as StripeSubscription;
use function config;

/**
 * Update or insert a subscription.
 */
class UpsertSubscription extends Job
{
    /** @var StripeSubscription|string $stripeSubscription The subscription object or id */
    public StripeSubscription $stripeSubscription;

    /**
     * @param StripeSubscription $stripeSubscription The subscription object or id.
     */
    public function __construct(StripeSubscription $stripeSubscription)
    {
        $this->stripeSubscription = $stripeSubscription;
    }

    public function handle(): Subscription
    {
        $converted = Subscription::fromStripe($this->stripeSubscription);

        if (config('stripe.sandbox') && !User::query()->where('stripe_id')->exists()) {
            return new Subscription();
        }

        /** @var Subscription $subscription */
        $subscription = Subscription::query()
            ->updateOrCreate(
                ['id' => $converted->id],
                $converted->toArray(),
            );

        return $subscription;
    }
}
