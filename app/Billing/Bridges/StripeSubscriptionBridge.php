<?php

declare(strict_types=1);

namespace Railgun\Billing\Bridges;

use Carbon\Carbon;
use Railgun\Models\Subscription;
use Stripe\Customer;
use Stripe\Subscription as StripeSubscription;
use function is_string;

class StripeSubscriptionBridge
{
    /**
     * Convert a Stripe subscription object to an Eloquent one.
     *
     * @param StripeSubscription $stripe The Stripe subscription.
     *
     * @return Subscription
     */
    public static function toEloquent(StripeSubscription $stripe): Subscription
    {
        $subscription       = new Subscription();
        $subscription->id   = $stripe->id;
        $subscription->plan = $stripe->plan->id;

        if (is_string($stripe->customer)) {
            $subscription->customer_id = $stripe->customer;
        } elseif ($stripe->customer instanceof Customer) {
            $subscription->customer_id = $stripe->customer->id;
        }

        $subscription->status = $stripe->status;

        if ($stripe->status === StripeSubscription::STATUS_INCOMPLETE
            && ($stripe->latest_invoice->payment_intent->client_secret)) {
            $subscription->payment_intent_secret = $stripe->latest_invoice->payment_intent->client_secret;
        }

        $subscription->current_period_start = Carbon::createFromTimestamp($stripe->current_period_start);
        $subscription->current_period_end   = Carbon::createFromTimestamp($stripe->current_period_end);
        $subscription->created_at           = Carbon::createFromTimestamp($stripe->created);
        $subscription->updated_at           = Carbon::createFromTimestamp($stripe->created);

        $cancelledAt = $stripe->canceled_at ?? $stripe->cancel_at;
        if ($cancelledAt !== null) {
            $subscription->cancelled_at = Carbon::createFromTimestamp($cancelledAt);
        }

        return $subscription;
    }
}
