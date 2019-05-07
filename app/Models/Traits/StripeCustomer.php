<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;
use function is_string;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Stripe\Customer;
use Stripe\Error\InvalidRequest;
use Stripe\Subscription;

/**
 * Trait StripeCustomer.
 * Contains all functions to access user's Stripe customer profile.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 *
 * @property Customer $stripe
 */
trait StripeCustomer
{
    /**
     * stripe attribute.
     *
     * @return Customer|null
     */
    public function getStripeAttribute(): ?Customer
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
        if ($this->stripe_id === null) {
            return false;
        } else if (!$strict) {
            return true;
        }

        return $this->getStripeCustomer() !== null;
    }


    /**
     * @return Customer
     */
    public function getStripeCustomer(): ?Customer
    {
        return Cache::remember($this->getStripeCustomerCacheKey(), 5, function () {
            try {
                return Customer::retrieve($this->stripe_id);
            } catch (InvalidRequest $e) {
                $this->stripe_id = null;
                $this->save();
                return null;
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
     * @return bool
     * @throws Http400BadRequestException
     */
    public function hasStripeSubscription($plans)
    {
        if (is_string($plans)) {
            $plans = [$plans];
        }

        if ($this->isStripeCustomer(true)) {
            /** @var Subscription $subscription */
            foreach ($this->getStripeSubscriptions() as $subscription) {
                if (
                    in_array($subscription->plan->id, $plans, true) &&
                    in_array($subscription->status, ["trialing", "active"], true)
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
     * @return Subscription[]
     * @throws Http400BadRequestException
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
     * @throws Http400BadRequestException
     */
    public function requireStripeCustomer(bool $strict = false)
    {
        if (!$this->isStripeCustomer($strict)) {
            throw new Http400BadRequestException(null, "User with id `{$this->id}` is not a Stripe customer");
        }
    }
}
