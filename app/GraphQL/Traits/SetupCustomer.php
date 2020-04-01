<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Traits;

use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;

/**
 * Allow queries to setup a Stripe customer based on the current logged user.
 */
trait SetupCustomer
{
    /**
     * Setup the customer (create the Stripe customer if it does not exists), and attach the $paymentMethodId if
     * necessary.
     *
     * @param User   $user            The current user.
     * @param string $paymentMethodId The payment method ID, retrieve through Stripe.js.
     *
     * @return User The current user, as a Stripe customer.
     *
     * @throws ApiErrorException
     */
    private function setupCustomer(User $user, string &$paymentMethodId): User
    {
        if ($user->isStripeCustomer()) {
            $user->attachPaymentMethod($paymentMethodId);
        } else {
            $customer = $user->createCustomer($paymentMethodId);

            $paymentMethodId = (string)$customer->invoice_settings->default_payment_method;
        }

        return $user;
    }
}
