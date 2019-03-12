<?php

namespace App\Models\Traits;

use App\Exceptions\Http400InvalidRequestException;
use Illuminate\Support\Facades\Cache;

/**
 * Trait StripeCustomer.
 * Contains all functions to access user's Stripe customer profile.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Models\Traits
 */
trait StripeCustomer
{
    /**
     * stripe attribute.
     *
     * @return \Stripe\Customer|null
     */
    public function getStripeAttribute(): ?\Stripe\Customer
    {
        return $this->isStripeCustomer() ? $this->getStripeCustomer() : null;
    }


    /**
     * Check if the user is already a Stripe customer.
     *
     * @param bool $strict also check on the API
     *
     * @return bool
     */
    public function isStripeCustomer($strict = false): bool
    {
        if (app()->environment() === "offline") {
            return $this->stripe_id === null;
        }

        if ($this->stripe_id === null) {
            return false;
        } else if (!$strict) {
            return true;
        }

        return $this->getStripeCustomer() !== null;
    }


    /**
     * @return \Stripe\Customer
     */
    public function getStripeCustomer(): ?\Stripe\Customer
    {
        if (app()->environment() === "offline") {
            return new \Stripe\Customer();
        }

        return Cache::remember($this->getStripeCustomerCacheKey(), 5, function () {
            try {
                return \Stripe\Customer::retrieve($this->stripe_id);
            } catch (\Stripe\Error\InvalidRequest $e) {
                $this->stripe_id = null;
                $this->save();

                return;
            }
        });
    }


    /**
     * Get the Brain customer cache used to get customer information from the cache.
     *
     * @return string
     */
    private function getStripeCustomerCacheKey()
    {
        return "stripe-customer-" . $this->stripe_id;
    }


    /**
     * Delete the Stripe customer from the cache.
     */
    public function forgetStripeCustomerCache()
    {
        Cache::forget($this->getStripeCustomerCacheKey());
    }


    /**
     * Check if the user has subscribed to the given plan.
     *
     * @param string|array $plans
     *
     * @throws Http400InvalidRequestException
     *
     * @return bool
     */
    public function hasStripeSubscription($plans)
    {
        // Emulate that the user has all plans if environment is offline
        if (app()->environment() === "offline") {
            return $this->stripe_id === null;
        }

        if (\is_string($plans)) {
            $plans = [$plans];
        }

        if ($this->isStripeCustomer(true)) {
            /** @var \Stripe\Subscription $subscription */
            foreach ($this->getStripeSubscriptions() as $subscription) {
                if (
                    \in_array($subscription->plan->id, $plans, true) &&
                    \in_array($subscription->status, ["trialing", "active"], true)
                ) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Get the user subscriptions.
     *
     * @throws Http400InvalidRequestException
     *
     * @return \Stripe\Subscription[]
     */
    public function getStripeSubscriptions(): array
    {
        $this->requireStripeCustomer();

        return $this->getStripeCustomer()->subscriptions->data;
    }


    /**
     * Throw an Http400InvalidRequestException if the user is not a Stripe cusstomer.
     *
     * @param bool $strict
     *
     * @throws Http400InvalidRequestException
     */
    public function requireStripeCustomer(bool $strict = false)
    {
        if (!$this->isStripeCustomer($strict)) {
            throw new Http400InvalidRequestException("User with id `{$this->id}` is not a Stripe customer");
        }
    }
}
