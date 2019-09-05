<?php

namespace App\Services\Stripe\Models;

use Illuminate\Support\Facades\Cache;
use Stripe\Customer;
use Stripe\Error\Api;
use Stripe\Subscription;

/**
 * Trait StripeCustomer.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 *
 * @property string $stripe_id The Stripe customer id. Starts with "cus_".
 */
trait StripeCustomer
{
    /**
     * Check if the user is a Stripe Customer.
     *
     * @param bool $strict Check against the Stripe API.
     *
     * @return bool
     */
    public function isStripeCustomer(bool $strict = false): bool
    {
        if ($this->stripe_id === null) {
            return false;
        } else if (!$strict) {
            return true;
        } else {
            return $this->getStripeCustomer() instanceof Customer;
        }
    }


    /**
     * Create a new Stripe Customer using the user's information.
     *
     * @param string|null $source
     *
     * @return Customer
     */
    public function createStripeCustomer(string $source = null): Customer
    {
        $customer = Customer::create([
            "email" => $this->email,
            "metadata" => [
                "user_id" => $this->id
            ],
            "name" => $this->full_name,
            "source" => $source
        ]);

        $this->stripe_id = $customer->id;
        $this->save();

        return $customer;
    }


    /**
     * Get the user's associate Stripe Customer.
     *
     * @param bool $createIfNeeded
     *
     * @return Customer|null
     */
    public function asStripeCustomer(bool $createIfNeeded = false)
    {
        $isStripeCustomer = $this->isStripeCustomer(false);

        if (!$isStripeCustomer && $createIfNeeded) {
            return $this->createStripeCustomer();
        } else if ($isStripeCustomer) {
            return Customer::retrieve($this->stripe_id);
        } else {
            return null;
        }
    }


    /**
     * @return Customer
     */
    public function getStripeCustomer(): ?Customer
    {
        return Cache::remember(self::getStripeCustomerCacheKey(), 24 * 3600, function () {
            try {
                $customer = Customer::retrieve($this->stripe_id);

                return $customer;
            } catch (Api $exception) {
                $this->stripe_id = null;
                $this->save();

                return null;
            }
        });
    }


    /**
     * Get the Stripe Customer cache key to store customer information from the cache.
     *
     * @return string
     */
    private function getStripeCustomerCacheKey()
    {
        return "user.{$this->id}.stripe";
    }


    /**
     * Delete the Braintree customer from the cache.
     */
    public function forgetStripeCustomerCache()
    {
        Cache::forget($this->getStripeCustomerCacheKey());
    }


    public function getStripeSubscriptions()
    {
        return Subscription::all([
            "customer" => $this->stripe_id
        ], [
            "expand" => ["default_source"]
        ]);
    }


    public function addSource(string $token)
    {
        Customer::createSource($this->stripe_id, [
            "source" => $token
        ]);
        $this->forgetStripeCustomerCache();
    }
}
