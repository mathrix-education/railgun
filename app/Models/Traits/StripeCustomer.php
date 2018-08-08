<?php
namespace App\Models\Traits;

use App\Exceptions\Http400InvalidRequestException;
use Illuminate\Support\Facades\Cache;

/**
 * Trait StripeCustomer
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Models\Stripe
 * @since
 * @copyright Mathrix Education SA
 */
trait StripeCustomer
{
    /**
     * Get the Brain customer cache used to get customer information from the cache
     *
     * @return string
     */
    private function getStripeCustomerCacheKey()
    {
        return "stripe-customer-" . $this->stripe_id;
    }


    /**
     * Delete the Stripe customer from the cache
     */
    public function forgetStripeCustomerCache()
    {
        Cache::forget($this->getStripeCustomerCacheKey());
    }


    /**
     * Check if the user is already a Stripe customer
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
        } else {
            return $this->getStripeCustomer() !== null;
        }
    }


    /**
     * Throw an Http400InvalidRequestException if the user is not a Stripe cusstomer
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


    /**
     * @return \Stripe\Customer
     */
    public function getStripeCustomer(): ?\Stripe\Customer
    {
        return Cache::remember($this->getStripeCustomerCacheKey(), 5, function () {
            try {
                return \Stripe\Customer::retrieve($this->stripe_id);
            } catch (\Stripe\Error\InvalidRequest $e) {
                $this->stripe_id = null;
                $this->save();
                return null;
            }
        });
    }


    /**
     * Get the user subscriptions
     *
     * @return \Stripe\Subscription[]
     * @throws Http400InvalidRequestException
     */
    public function getStripeSubscriptions(): array
    {
        $this->requireStripeCustomer();

        return $this->getStripeCustomer()->subscriptions->data;
    }


    /**
     * Check if the user has subscribed to the given plan
     *
     * @param string|array $plans
     *
     * @return bool
     * @throws Http400InvalidRequestException
     */
    public function hasStripeSubscription($plans)
    {
        if (is_string($plans)) {
            $plans = [$plans];
        }

        if ($this->isStripeCustomer(true)) {
            /** @var \Stripe\Subscription $subscription */
            foreach ($this->getStripeSubscriptions() as $subscription) {
                if (
                    in_array($subscription->plan->id, $plans) &&
                    in_array($subscription->status, ["trialing", "active"])
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
