<?php

namespace App\Services\Braintree\Models;

use App\Exceptions\InvalidBraintreeCustomerException;
use App\Models\User;
use Braintree\Customer;
use Braintree\Exception\NotFound as BraintreeNotFound;
use Braintree\PaymentMethod;
use Braintree\Subscription;
use Braintree\Transaction;
use Braintree\TransactionSearch;
use Faker\Factory as Fakerfactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Trait BraintreeCustomer.
 * Contains all functions to access user"s Braintree customer profile.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
trait BraintreeCustomer
{
    /**
     * Check if the user is already a Braintree customer.
     *
     * @param bool $strict also check on the API
     *
     * @return bool
     */
    public function isBraintreeCustomer($strict = false): bool
    {
        if ($this->braintree_id === null) {
            return false;
        } else if (!$strict) {
            return true;
        } else {
            return $this->getBraintreeCustomer() instanceof Customer;
        }
    }


    /**
     * Create a Braintree customer associated with this user account.
     * Should only is used to create customer during tests, since we have migrated to Stripe in September 2019.
     *
     * @param string|null $paymentMethodNonce a payment nonce to attach
     *
     * @return Customer
     * @throws Throwable
     */
    public function createBraintreeCustomer(string $paymentMethodNonce = null): Customer
    {
        if (!$this->isBraintreeCustomer(true)) {
            $braintreeId = "cus_" . str_replace("-", "", Fakerfactory::create()->uuid);

            $result = gateway()->customer()->create([
                "id" => $braintreeId,
                "email" => $this->email,
                "firstName" => $this->firstname,
                "lastName" => $this->lastname,
                "paymentMethodNonce" => $paymentMethodNonce ?? null,
            ]);

            throw_if(!$result->success, new \Exception("Unable to create braintree customer"));

            $this->braintree_id = $braintreeId;
            $this->save();
        }

        return $this->getBraintreeCustomer();
    }


    /**
     * braintree attribute.
     *
     * @return Customer|null
     */
    public function asBraintreeCustomer(): ?Customer
    {
        return $this->isBraintreeCustomer() ? $this->getBraintreeCustomer() : null;
    }


    /**
     * @return Customer
     */
    public function getBraintreeCustomer(): ?Customer
    {
        return Cache::remember(self::getBraintreeCustomerCacheKey(), 24 * 3600, function () {
            try {
                return gateway()->customer()->find($this->braintree_id);
            } catch (BraintreeNotFound $e) {
                $this->braintree_id = null;
                $this->save();

                return null;
            }
        });
    }


    /**
     * Get the Braintree customer cache used to get customer information from the cache.
     *
     * @return string
     */
    private function getBraintreeCustomerCacheKey()
    {
        return "user.{$this->id}.braintree";
    }


    /**
     * Throws an InvalidBraintreeCustomerException if the user is not a Braintree customer.
     *
     * @param bool $strict
     *
     * @throws InvalidBraintreeCustomerException
     */
    public function requireBraintreeCustomer(bool $strict = false)
    {
        if (!$this->isBraintreeCustomer($strict)) {
            /** @var User $this */
            throw new InvalidBraintreeCustomerException($this);
        }
    }


    /**
     * Get user's Braintree payment methods.
     *
     * @return array
     * @throws InvalidBraintreeCustomerException
     */
    public function getBraintreePaymentMethods()
    {
        $this->requireBraintreeCustomer();

        return $this->getBraintreeCustomer()->paymentMethods;
    }


    /**
     * Delete the Braintree customer from the cache.
     */
    public function forgetBraintreeCustomerCache()
    {
        Cache::forget($this->getBraintreeCustomerCacheKey());
    }


    /**
     * Check if the user has subscribed to the given plan.
     *
     * @param string|array $plans
     *
     * @return bool
     * @throws InvalidBraintreeCustomerException
     */
    public function hasBraintreeSubscription($plans)
    {
        if (is_string($plans)) {
            $plans = [$plans];
        }
        if ($this->isBraintreeCustomer(true)) {
            /** @var Subscription $subscription */
            foreach ($this->getBraintreeSubscriptions() as $subscription) {
                $validPlan = in_array($subscription->planId, $plans, true);
                $validStatus = in_array($subscription->status, [Subscription::ACTIVE, Subscription::PAST_DUE]);
                if ($validPlan && $validStatus) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Get the user subscriptions.
     *
     * @return Collection|Subscription[]
     * @throws InvalidBraintreeCustomerException
     */
    public function getBraintreeSubscriptions(): Collection
    {
        $this->requireBraintreeCustomer();

        $subscriptions = new Collection();

        if (!$this->isBraintreeCustomer()) {
            return $subscriptions;
        }

        /** @var PaymentMethod $paymentMethod */
        foreach ($this->getBraintreeCustomer()->paymentMethods as $paymentMethod) {
            /** @var Subscription $subscription */
            foreach ($paymentMethod->subscriptions as $subscription) {
                $subscriptions->push($subscription);
            }
        }

        return $subscriptions;
    }


    /**
     * Get customer's Braintree transactions
     *
     * @return Collection
     * @throws InvalidBraintreeCustomerException
     */
    public function getBraintreeTransactions()
    {
        $this->requireBraintreeCustomer();
        $transactionResults = Transaction::search([
            TransactionSearch::customerId()->is($this->braintree_id),
        ]);
        $transactions = new Collection();
        foreach ($transactionResults as $transaction) {
            $transactions->push($transaction);
        }

        return $transactions;
    }
}
