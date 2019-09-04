<?php

namespace App\Services\Stripe;

use Stripe\Customer;
use Stripe\Error\Api;

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
     * @param bool $checkAgainstStripeAPI Check against the Stripe API.
     *
     * @return bool
     */
    public function isStripeCustomer(bool $checkAgainstStripeAPI = false): bool
    {
        $hasStripeId = !empty($this->stripe_id);
        if (!$checkAgainstStripeAPI) {
            return $hasStripeId;
        } else if (!$hasStripeId) {
            return false;
        } else {
            try {
                $customer = Customer::retrieve($this->stripe_id);

                return $this->stripe_id === $customer->id;
            } catch (Api $exception) {
                $this->stripe_id = null;
                $this->save();

                return false;
            }
        }
    }


    /**
     * Create a new Stripe Customer using the user's information.
     *
     * @return Customer
     */
    public function createStripeCustomer(): Customer
    {
        $customer = Customer::create([
            "email" => $this->email,
            "metadata" => [
                "user_id" => $this->id
            ],
            "name" => $this->full_name
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
}
