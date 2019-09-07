<?php

namespace App\Services\Stripe\Models;

use Illuminate\Support\Collection;
use Stripe\Customer;
use Stripe\Error\Api;
use Stripe\Source;
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
    public static $StripeCustomerCacheKey = "stripe.customer";
    public static $StripeSubscriptionsCacheKey = "stripe.subscriptions";


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
            return $this->asStripeCustomer() instanceof Customer;
        }
    }


    /**
     * Create a new Stripe Customer using the user's information.
     *
     * @param string|null $source The default customer source.
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
     * Get the user's associated Stripe customer.
     *
     * @return Customer|null
     */
    public function asStripeCustomer()
    {
        if (!$this->isStripeCustomer()) {
            return null;
        }

        return $this->cache(StripeCustomer::$StripeCustomerCacheKey, 7 * 24 * 3600, function () {
            try {
                return Customer::retrieve($this->stripe_id);
            } catch (Api $exception) {
                $this->stripe_id = null;
                $this->save();

                return null;
            }
        });
    }


    /**
     * Get the user's Stripe subscriptions.
     *
     * @return Collection|Subscription[]
     */
    public function getStripeSubscriptions(): Collection
    {
        if (!$this->isStripeCustomer()) {
            return new Collection();
        }

        return $this->cache(StripeCustomer::$StripeSubscriptionsCacheKey, 7 * 24 * 3600, function () {
            $subscriptions = new Collection();

            $stripeCollection = Subscription::all([
                "customer" => $this->stripe_id,
                "expand" => [
                    "data.default_source",
                    "data.latest_invoice.payment_intent",
                    "data.pending_setup_intent",
                ]
            ]);

            foreach ($stripeCollection as $subscription) {
                $subscriptions->push($subscription);
            }

            return $subscriptions;
        });
    }


    /**
     * Add a source to the user.
     *
     * @param string $token
     * @param bool $makeDefault
     */
    public function addSource(string $token, bool $makeDefault = false)
    {
        /** @var Source $source */
        $source = Customer::createSource($this->stripe_id, [
            "source" => $token
        ]);

        if ($makeDefault) {
            Customer::update($this->stripe_id, [
                "default_source" => $source->id
            ]);
        }

        $this->invalidate(StripeCustomer::$StripeCustomerCacheKey);
    }
}
