<?php

namespace App\Models\Traits;

use App\Services\Braintree\Exceptions\BraintreeErrorHandler;
use App\Services\Braintree\Exceptions\BraintreeGatewayException;
use App\Services\Braintree\Exceptions\BraintreeProcessorException;
use App\Services\Braintree\Utils\Formatter;
use Braintree\Customer;
use Braintree\Exception\NotFound as BraintreeNotFound;
use Braintree\PaymentMethod;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Subscription;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

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
     * braintree attribute.
     *
     * @return Customer|null
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
        } else {
            return $this->getBraintreeCustomer() instanceof Customer;
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
     * Get user"s Braintree payment methods.
     *
     * @return array
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
     * @throws Http400BadRequestException
     */
    public function requireBraintreeCustomer(bool $strict = false)
    {
        if (!$this->isBraintreeCustomer($strict)) {
            throw new Http400BadRequestException(null, "User with id `{$this->id}` is not a Braintree customer");
        }
    }


    /**
     * Get the user"s default payment method token.
     *
     * @return string
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
     * @param string $paymentMethodNonce The payment method nonce.
     * @param bool $makeDefault If the new payment method should be the default payment method.
     *
     * @return PaymentMethod
     * @throws Http400BadRequestException
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     */
    public function addBraintreePaymentMethod(string $paymentMethodNonce, bool $makeDefault = true)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        /** @var Successful|Error $result */
        $result = PaymentMethod::create([
            "customerId" => $this->braintree_id,
            "paymentMethodNonce" => $paymentMethodNonce,
            "options" => [
                "makeDefault" => $makeDefault
            ]
        ]);

        if (!$result->success) {
            BraintreeErrorHandler::report($result);
        }

        return $result->paymentMethod;
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
     * @throws Http400BadRequestException
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
     * Check if the user owns the given subscription id.
     *
     * @param string $subscriptionId The Braintree subscriptionId.
     *
     * @return bool
     * @throws Http400BadRequestException
     */
    public function ownsBraintreeSubscriptionId(string $subscriptionId)
    {
        $subscriptionIds = $this->getBraintreeSubscriptions()->map(function (Subscription $subscription) {
            return $subscription->id;
        });

        return $subscriptionIds->search($subscriptionId) !== false;
    }


    /**
     * Braintree lock.
     *
     * @return $this
     */
    public function braintreeLock()
    {
        $this->forgetBraintreeCustomerCache();
        $this->settings = array_merge($this->settings, ["braintree_lock" => true]);
        $this->save();
        $this->refresh();

        return $this;
    }


    /**
     * Braintree unlock.
     *
     * @return $this
     */
    public function braintreeUnlock()
    {
        $this->forgetBraintreeCustomerCache();
        $this->settings = array_merge($this->settings, ["braintree_lock" => false]);
        //$this->settings = Arr::except($this->settings, ["braintree_lock"]);
        $this->save();
        $this->refresh();

        return $this;
    }


    /**
     * Braintree unlock.
     *
     * @return bool
     */
    public function isBraintreeLocked(): bool
    {
        return isset($this->settings["braintree_lock"])
            && $this->settings["braintree_lock"] === true;
    }
}
