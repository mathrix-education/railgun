<?php
namespace App\Models\Braintree;


use App\Exceptions\Http400InvalidRequestException;
use Illuminate\Support\Facades\Cache;

/**
 * Trait Customer
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Models\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
trait Customer
{
    /**
     * Get the Brain customer cache used to get customer information from the cache
     *
     * @return string
     */
    private function getBraintreeCustomerCacheKey()
    {
        return "braintree-customer-" . $this->braintree_id;
    }


    /**
     * Delete the Braintree customer from the cache
     */
    public function forgetBraintreeCustomerCache()
    {
        Cache::forget($this->getBraintreeCustomerCacheKey());
    }


    /**
     * Check if the user is already a Braintree customer
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
            try {
                \Braintree_Customer::find($this->braintree_id);
                return true;
            } catch (\Braintree_Exception_NotFound $e) {
                return false;
            }
        }
    }


    /**
     * Throw an Http400InvalidRequestException if the user is not a Braintree cusstomer
     *
     * @param bool $strict
     *
     * @throws Http400InvalidRequestException
     */
    public function requireBraintreeCustomer(bool $strict = false)
    {
        if (!$this->isBraintreeCustomer($strict)) {
            throw new Http400InvalidRequestException("User with id `{$this->id}` is not a Braintree customer");
        }
    }


    /**
     * @return \Braintree_Customer
     */
    public function getBraintreeCustomer(): \Braintree_Customer
    {
        return Cache::remember(self::getBraintreeCustomerCacheKey(), 5, function () {
            return \Braintree_Customer::find($this->braintree_id);
        });
    }


    /**
     * Create a Braintree customer associated with this user account
     *
     * @param string|null $paymentMethodNonce a payment nonce to attach
     *
     * @return \Braintree_Customer
     */
    public function createBraintreeCustomer(string $paymentMethodNonce = null): \Braintree_Customer
    {
        /** @var \Braintree_Gateway $braintree */
        if (!$this->isBraintreeCustomer(true)) {
            $braintreeId = "cus_" . str_replace("-", "", \Faker\Factory::create()->uuid);

            /** @var \Braintree_Result_Successful|\Braintree_Result_Error $result */
            $result = \Braintree_Customer::create([
                "id" => $braintreeId,
                "email" => $this->email,
                "firstName" => $this->firstname,
                "lastName" => $this->lastname,
                "paymentMethodNonce" => $paymentMethodNonce ?? null
            ]);

            if ($result->success) {
                $this->braintree_id = $braintreeId;
                $this->save();
            }
        }

        return $this->getBraintreeCustomer();
    }


    /**
     * Get user's Braintree payment methods
     *
     * @return array
     * @throws Http400InvalidRequestException
     */
    public function getBraintreePaymentMethods()
    {
        $this->requireBraintreeCustomer();

        return $this->getBraintreeCustomer()->paymentMethods;
    }


    /**
     * Get the user's default payment method token
     *
     * @return string
     * @throws Http400InvalidRequestException
     */
    public function getBraintreeDefaultPaymentMethodToken()
    {
        $this->requireBraintreeCustomer();

        return $this->getBraintreeCustomer()->defaultPaymentMethod()->token;
    }


    /**
     * Attach a new payment method to the current user
     *
     * @param string $paymentMethodNonce
     *
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     * @throws Http400InvalidRequestException
     */
    public function addBraintreePaymentMethod(string $paymentMethodNonce)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        return \Braintree_PaymentMethod::create([
            "customerId" => $this->braintree_id,
            "paymentMethodNonce" => $paymentMethodNonce
        ]);
    }


    /**
     * Delete a payment method with a token from the vault
     *
     * @param string $paymentMethodToken
     *
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     * @throws Http400InvalidRequestException
     */
    public function deleteBraintreePaymentMethod(string $paymentMethodToken)
    {
        $this->requireBraintreeCustomer();

        return \Braintree_PaymentMethod::delete($paymentMethodToken);
    }


    /**
     * Charge a user using its default payment method
     *
     * @param float $amount
     *
     * @throws Http400InvalidRequestException
     * @return \Braintree_Result_Successful|\Braintree_Result_Error
     */
    public function chargeWithBraintree(float $amount)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        return \Braintree_Transaction::sale([
            "amount" => $amount,
            "customerId" => $this->braintree_id,
            "transactionSource" => "merchant",
            "options" => [
                "submitForSettlement" => true
            ]
        ]);
    }


    /**
     * Subscribe to a plan
     *
     * @param string $plan
     *
     * @throws Http400InvalidRequestException
     */
    public function addBraintreeSubscription(string $plan)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        \Braintree_Subscription::create([
            "paymentMethodToken" => $this->getBraintreeCustomer()
                ->defaultPaymentMethod()
                ->token,
            "planId" => $plan
        ]);
    }


    /**
     * Get the user subscriptions
     *
     * @return \Braintree_Subscription[]
     * @throws Http400InvalidRequestException
     */
    public function getBraintreeSubscriptions(): array
    {
        $this->requireBraintreeCustomer();

        $subscriptions = [];

        /** @var \Braintree_CreditCard $creditCard */
        foreach ($this->getBraintreeCustomer()->creditCards as $creditCard) {
            /** @var \Braintree_Subscription $subscription */
            foreach ($creditCard->subscriptions as $subscription) {
                $subscriptions[] = $subscription;
            }
        }


        /** @var \Braintree_CreditCard $creditCard */
        foreach ($this->getBraintreeCustomer()->paypalAccounts as $paypalAccount) {
            /** @var \Braintree_Subscription $subscription */
            foreach ($paypalAccount->subscriptions as $subscription) {
                $subscriptions[] = $subscription;
            }
        }

        return $subscriptions;
    }


    /**
     * Check if the user has subscribed to the given plan
     *
     * @param string|array $plans
     *
     * @return bool
     * @throws Http400InvalidRequestException
     */
    public function hasBraintreeSubscription($plans)
    {
        if (is_string($plans)) {
            $plans = [$plans];
        }

        if ($this->isBraintreeCustomer()) {
            /** @var \Braintree_Subscription $subscription */
            foreach ($this->getBraintreeSubscriptions() as $subscription) {
                if (in_array($subscription->planId, $plans)) {
                    return true;
                }
            }
        }

        return false;
    }
}
