<?php

declare(strict_types=1);

namespace Tests\Utils;

use App\Models\User;
use App\Services\Braintree\Models\BraintreeCustomer;
use Braintree\Customer;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Faker\Factory as Fakerfactory;
use function gateway;
use function str_replace;

class BraintreeUtils
{
    /**
     * Create a Braintree customer profile associated with the given user.
     *
     * @param User        $user               The user.
     * @param string|null $paymentMethodNonce The default payment method nonce.
     *
     * @return Customer
     */
    public static function createCustomer(User $user, ?string $paymentMethodNonce = null): Customer
    {
        $braintreeId = 'cus_' . str_replace('-', '', Fakerfactory::create()->uuid);

        /** @var Successful|Error $result */
        $result = gateway()->customer()->create([
            'id' => $braintreeId,
            'email' => $user->email,
            'firstName' => $user->firstname,
            'lastName' => $user->lastname,
            'paymentMethodNonce' => $paymentMethodNonce,
        ]);

        $user->braintree_id = $braintreeId;
        $user->save();
        $user->invalidate(BraintreeCustomer::$BraintreeCustomerCacheKey);

        return $result->customer;
    }
}
