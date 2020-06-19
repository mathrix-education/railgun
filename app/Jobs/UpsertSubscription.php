<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Subscription as StripeSubscription;
use function assert;
use function config;
use function is_string;

/**
 * Update or insert a subscription.
 */
class UpsertSubscription extends Job
{
    /** @var StripeSubscription|string $stripeSubscription The subscription object or id */
    public $stripeSubscription;

    /**
     * @param StripeSubscription|string $stripeSubscription The subscription object or id.
     */
    public function __construct($stripeSubscription)
    {
        $this->stripeSubscription = $stripeSubscription;
    }

    public function handle(): Subscription
    {
        if (is_string($this->stripeSubscription)) {
            // If the subscription id was passed instead of the full object, retrieve from the Stripe API
            $this->stripeSubscription = StripeSubscription::retrieve($this->stripeSubscription);
        }

        assert($this->stripeSubscription instanceof StripeSubscription);

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
