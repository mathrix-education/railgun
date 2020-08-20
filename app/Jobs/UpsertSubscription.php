<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Braintree\Subscription as BraintreeSubscription;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Subscription as StripeSubscription;
use function config;

/**
 * Update or insert a subscription.
 */
class UpsertSubscription extends Job
{
    /** @var StripeSubscription|BraintreeSubscription $entry The subscription object. */
    public $entry;

    /**
     * @param StripeSubscription|BraintreeSubscription $entry The subscription object.
     */
    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function handle(): Subscription
    {
        if ($this->entry instanceof BraintreeSubscription) {
            $converted = Subscription::fromBraintree($this->entry);
        } elseif ($this->entry instanceof StripeSubscription) {
            $converted = Subscription::fromStripe($this->entry);

            if (config('stripe.sandbox') && !User::query()->where('stripe_id')->exists()) {
                return new Subscription(); // @codeCoverageIgnore
            }
        } else {
            /** @var Subscription $converted */
            $converted = $this->entry;
        }

        // Do not override existing user_id
        $data = $converted->toArray();
        if ($data['user_id'] === null) {
            unset($data['user_id']);
        }

        /** @var Subscription $subscription */
        $subscription = Subscription::query()->updateOrCreate(['id' => $converted->id], $data);

        $subscription->flushCache();

        return $subscription;
    }
}
