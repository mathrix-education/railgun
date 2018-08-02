<?php
namespace App\Models\Braintree;


use App\Exceptions\Http400InvalidRequestException;
use Braintree\Exception\NotFound;
use Illuminate\Support\Facades\Cache;

/**
 * Trait Customer
 *
 * @author Mathrix Back-End Team
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
     * Get the Braintree Gateway
     *
     * @return \Braintree\Gateway
     */
    private function getBraintreeGateway(): \Braintree\Gateway
    {
        return app()->make(\Braintree\Gateway::class);
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
                $this->getBraintreeGateway()
                    ->customer()
                    ->find($this->braintree_id);
                return true;
            } catch (NotFound $e) {
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
     * @return \Braintree\Customer
     */
    public function getBraintreeCustomer(): \Braintree\Customer
    {
        return Cache::remember(self::getBraintreeCustomerCacheKey(), 5, function () {
            return $this->getBraintreeGateway()
                ->customer()
                ->find($this->braintree_id);
        });
    }


    /**
     * Create a Braintree customer associated with this user account
     *
     * @param string|null $paymentMethodNonce a payment nonce to attach
     *
     * @return \Braintree\Customer
     */
    public function createBraintreeCustomer(string $paymentMethodNonce = null): \Braintree\Customer
    {
        /** @var \Braintree\Gateway $braintree */
        if (!$this->isBraintreeCustomer(true)) {
            $faker = \Faker\Factory::create();

            $braintreeId = $faker->uuid;

            /** @var \Braintree\Result\Successful|\Braintree\Result\Error $result */
            $result = $this->getBraintreeGateway()
                ->customer()
                ->create([
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
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     * @throws Http400InvalidRequestException
     */
    public function addBraintreePaymentMethod(string $paymentMethodNonce)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        return $this->getBraintreeGateway()
            ->paymentMethod()
            ->create([
                "customerId" => $this->braintree_id,
                "paymentMethodNonce" => $paymentMethodNonce
            ]);
    }


    /**
     * Delete a payment method with a token from the vault
     *
     * @param string $paymentMethodToken
     *
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     * @throws Http400InvalidRequestException
     */
    public function deleteBraintreePaymentMethod(string $paymentMethodToken)
    {
        $this->requireBraintreeCustomer();

        return $this->getBraintreeGateway()
            ->paymentMethod()
            ->delete($paymentMethodToken);
    }


    /**
     * Charge a user using its default payment method
     *
     * @param float $amount
     *
     * @throws Http400InvalidRequestException
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function chargeWithBraintree(float $amount)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        return $this->getBraintreeGateway()->transaction()->sale([
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

        $this->getBraintreeGateway()->subscription()->create([
            "paymentMethodToken" => $this->getBraintreeCustomer()
                ->defaultPaymentMethod()
                ->token,
            "planId" => $plan
        ]);
    }


    /**
     * Get the user subscriptions
     *
     * @return \Braintree\Subscription[]
     * @throws Http400InvalidRequestException
     */
    public function getBraintreeSubscriptions(): array
    {
        $this->requireBraintreeCustomer();

        $subscriptions = [];

        /** @var \Braintree\CreditCard $creditCard */
        foreach ($this->getBraintreeCustomer()->creditCards as $creditCard) {
            /** @var \Braintree\Subscription $subscription */
            foreach ($creditCard->subscriptions as $subscription) {
                $subscriptions[] = $subscription;
            }
        }


        /** @var \Braintree\CreditCard $creditCard */
        foreach ($this->getBraintreeCustomer()->paypalAccounts as $paypalAccount) {
            /** @var \Braintree\Subscription $subscription */
            foreach ($paypalAccount->subscriptions as $subscription) {
                $subscriptions[] = $subscription;
            }
        }

        return $subscriptions;
    }


    /**
     * Check if the user has subscribed to the given plan
     *
     * @param string|array $plan
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
            /** @var \Braintree\Subscription $subscription */
            foreach ($this->getBraintreeSubscriptions() as $subscription) {
                if (in_array($subscription->planId, $plans)) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Cancel a subscription on Braintree
     *
     * @param $subscriptionId
     *
     * @throws Http400InvalidRequestException
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function cancelBraintreeSubscription(string $subscriptionId)
    {
        $this->requireBraintreeCustomer();
        $this->forgetBraintreeCustomerCache();

        return $this->getBraintreeGateway()
            ->subscription()
            ->cancel($subscriptionId);
    }
}
