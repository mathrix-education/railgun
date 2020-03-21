<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Traits;

use Railgun\Models\User;
use Stripe\Exception\ApiErrorException;

trait SetupCustomer
{
    /**
     * Setup the customer (create the Stripe customer if it does not exists), and attach the $paymentMethodId if
     * necessary.
     *
     * @param User   $user            The user.
     * @param string $paymentMethodId The payment method ID, retrieve through Stripe.js.
     *
     * @return User
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
