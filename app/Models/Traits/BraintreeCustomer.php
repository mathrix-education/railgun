<?php

namespace App\Models\Traits;

use App\Services\Braintree\Utils\Formatter;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Exception\NotFound as BraintreeNotFound;
use Braintree\PaymentMethod;
use Braintree\PayPalAccount;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Subscription;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Trait BraintreeCustomer.
 * Contains all functions to access user's Braintree customer profile.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
trait BraintreeCustomer
{
    /**
     * braintree attribute.
     *
     * @return Customer|null
     * @throws BraintreeNotFound
     */
    public function getBraintreeAttribute(): ?Customer
    {
        return $this->isBraintreeCustomer() ? $this->getBraintreeCustomer() : null;
    }


    /**
     * Check if the user is already a Braintree customer.
     *
     * @param bool $strict also check on the API
     *
     * @return bool
     */
    public function isBraintreeCustomer($strict = false): bool
    {
        if (app()->environment() === "offline") {
            return $this->braintree_id === null;
        }

        if ($this->braintree_id === null) {
            return false;
        } else if (!$strict) {
            return true;
        }

        try {
            Customer::find($this->braintree_id);

            return true;
        } catch (BraintreeNotFound $e) {
            return false;
        }
    }


    /**
     * @return Customer
     */
    public function getBraintreeCustomer(): ?Customer
    {
        if (app()->environment() === "offline") {
            return Customer::factory([]);
        }

        return Cache::remember(self::getBraintreeCustomerCacheKey(), 60 * 24 * 7, function () {
            try {
                return Customer::find($this->braintree_id);
            } catch (BraintreeNotFound $e) {
                $this->braintree_id = null;
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
    private function getBraintreeCustomerCacheKey()
    {
        return "braintree-customer-" . $this->braintree_id;
    }


    /**
     * Create a Braintree customer associated with this user account.
     *
     * @param string|null $paymentMethodNonce a payment nonce to attach
     *
     * @return Customer
     * @throws BraintreeNotFound
     * @throws Http400BadRequestException
     */
    public function createBraintreeCustomer(string $paymentMethodNonce = null): Customer
    {
        if (!$this->isBraintreeCustomer(true)) {
            $braintreeId = "cus_" . str_replace("-", "", FakerFactory::create()->uuid);

            /** @var Successful|Error $result */
            $result = Customer::create([
                "id" => $braintreeId,
                "email" => $this->email,
                "firstName" => $this->firstname,
                "lastName" => $this->lastname,
                "paymentMethodNonce" => $paymentMethodNonce ?? null,
            ]);

            if ($result->success) {
                $this->braintree_id = $braintreeId;
                $this->save();
            } else {
                /** @var Error $result */
                $this->record("braintree", "customer_creation", "error", $result->jsonSerialize());

                throw new Http400BadRequestException(
                    Formatter::cast($result),
                    "Unable to create Braintree customer"
                );
            }
        }

        return $this->getBraintreeCustomer();
    }


    /**
     * Get user's Braintree payment methods.
     *
     * @return array
     * @throws BraintreeNotFound
     * @throws Http400BadRequestException
     */
    public function getBraintreePaymentMethods()
    {
        $this->requireBraintreeCustomer();

        return $this->getBraintreeCustomer()->paymentMethods;
    }


    /**
     * Throw an Http400InvalidRequestException if the user is not a Braintree customer.
     *
     * @param bool $strict
     *
     * @throws BraintreeNotFound
     * @throws Http400BadRequestException
     */
    public function requireBraintreeCustomer(bool $strict = false)
    {
        if (!$this->isBraintreeCustomer($strict)) {
            throw new Http400BadRequestException(null, "User with id `{$this->id}` is not a Braintree customer");
        }
    }


    /**
     * Get the user's default payment method token.
     *
     * @return string
     * @throws BraintreeNotFound
     * @throws Http400BadRequestException
     */
    public function getBraintreeDefaultPaymentMethodToken()
    {
        $this->requireBraintreeCustomer();

        return $this->getBraintreeCustomer()
            ->defaultPaymentMethod()
            ->token;
    }


    /**
     * Attach a new payment method to the current user.
     *
     * @param string $paymentMethodNonce
     *
     * @return Successful|Error
     * @throws BraintreeNotFound
     * @throws Http400BadRequestException
     */
    public function addBraintreePaymentMethod(string $paymentMethodNonce)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        return PaymentMethod::create([
            "customerId" => $this->braintree_id,
            "paymentMethodNonce" => $paymentMethodNonce,
        ]);
    }


    /**
     * Delete the Braintree customer from the cache.
     */
    public function forgetBraintreeCustomerCache()
    {
        Cache::forget($this->getBraintreeCustomerCacheKey());
    }


    /**
     * Delete a payment method with a token from the vault.
     *
     * @param string $paymentMethodToken
     *
     * @return Successful|Error
     * @throws Http400BadRequestException
     * @throws BraintreeNotFound
     */
    public function deleteBraintreePaymentMethod(string $paymentMethodToken)
    {
        $this->requireBraintreeCustomer();

        return PaymentMethod::delete($paymentMethodToken);
    }


    /**
     * Check if the user has subscribed to the given plan.
     *
     * @param string|array $plans
     *
     * @return bool
     * @throws BraintreeNotFound
     * @throws Http400BadRequestException
     */
    public function hasBraintreeSubscription($plans)
    {
        // Emulate that the user has all plans if environment is offline
        if (app()->environment() === "offline") {
            return $this->braintree_id === null;
        }

        if (is_string($plans)) {
            $plans = [$plans];
        }

        if ($this->isBraintreeCustomer(true)) {
            /** @var Subscription $subscription */
            foreach ($this->getBraintreeSubscriptions() as $subscription) {
                if (in_array($subscription->planId, $plans, true) && $subscription->status === "Active") {
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
     * @throws BraintreeNotFound
     * @throws Http400BadRequestException
     */
    public function getBraintreeSubscriptions(): Collection
    {
        $this->requireBraintreeCustomer();

        $subscriptions = new Collection();

        if ($this->isBraintreeCustomer()) {
            /** @var CreditCard $creditCard */
            foreach ($this->getBraintreeCustomer()->creditCards as $creditCard) {
                /** @var Subscription $subscription */
                foreach ($creditCard->subscriptions as $subscription) {
                    $subscriptions->add($subscription);
                }
            }

            /** @var PayPalAccount $paypalAccount */
            foreach ($this->getBraintreeCustomer()->paypalAccounts as $paypalAccount) {
                /** @var Subscription $subscription */
                foreach ($paypalAccount->subscriptions as $subscription) {
                    $subscriptions->add($subscription);
                }
            }
        }

        $subscriptions->map(function ($subscription) {
            return Formatter::cast($subscription);
        });

        return $subscriptions;
    }
}
