<?php

declare(strict_types=1);

namespace App\Services\Braintree\Models;

use Braintree\Customer;
use Braintree\Exception\NotFound as BraintreeNotFound;
use Braintree\PaymentMethod;
use Braintree\Subscription;
use Illuminate\Support\Collection;
use function gateway;

/**
 * Trait BraintreeCustomer.
 */
trait BraintreeCustomer
{
    public static $BraintreeCustomerCacheKey      = 'braintree.customer';
    public static $BraintreeSubscriptionsCacheKey = 'braintree.subscriptions';

    /**
     * Get the user's Braintree subscriptions.
     *
     * @return Collection|Subscription[]
     */
    public function getBraintreeSubscriptions() : Collection
    {
        if (! $this->isBraintreeCustomer()) {
            return new Collection();
        }

        return $this->cache(self::$BraintreeSubscriptionsCacheKey, 7 * 24 * 3600, function () {
            $subscriptions = new Collection();

            /** @var PaymentMethod $paymentMethod */
            foreach ($this->asBraintreeCustomer()->paymentMethods as $paymentMethod) {
                /** @var Subscription $subscription */
                foreach ($paymentMethod->subscriptions as $subscription) {
                    $subscriptions->push($subscription);
                }
            }

            return $subscriptions;
        });
    }

    /**
     * Check if the user is already a Braintree customer.
     *
     * @param bool $strict also check on the API
     *
     * @return bool
     */
    public function isBraintreeCustomer(bool $strict = false) : bool
    {
        if ($this->braintree_id === null) {
            return false;
        }

        if (! $strict) {
            return true;
        }

        return $this->getBraintreeCustomer() instanceof Customer;
    }

    /**
     * braintree attribute.
     */
    public function asBraintreeCustomer() : ?Customer
    {
        if (! $this->isBraintreeCustomer()) {
            return null;
        }

        return $this->cache(BraintreeCustomer::$BraintreeCustomerCacheKey, 7 * 24 * 3600, function () {
            try {
                return gateway()->customer()->find($this->braintree_id);
            } catch (BraintreeNotFound $e) {
                $this->braintree_id = null;
                $this->save();

                return null;
            }
        });
    }
}
