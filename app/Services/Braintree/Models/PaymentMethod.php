<?php

namespace App\Services\Braintree\Models;

use Braintree\CreditCard;
use Braintree\PaymentMethod as BraintreePaymentMethod;
use Braintree\PayPalAccount;
use Faker\Provider\Uuid;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class PaymentMethod.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 * ---
 * @property string $token
 * @property string $card_type
 * @property string $cardholder_name
 * @property string $bin The first 6 digits of the credit card (Bank Identification Number).
 * @property string $last4
 * @property string $expires_at The card expiration date
 * @property string $customer_id
 */
class PaymentMethod extends BaseModel
{
    public $incrementing = false; // Token is the primary key
    public $timestamps = false; // We manage this ourselves


    public static function createFromNonce(string $nonce, string $customerId)
    {
        $token = Uuid::uuid();

        return BraintreePaymentMethod::create([
            "token" => $token,
            "paymentMethodNonce" => $nonce,
            "customerId" => $customerId,
            "failOnDuplicatePaymentMethod" => true
        ]);
    }


    /**
     * @return CreditCard|PayPalAccount
     */
    public function asBraintreeObject()
    {
        return BraintreePaymentMethod::find($this->token);
    }
}
