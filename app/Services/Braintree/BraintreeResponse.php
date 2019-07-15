<?php


namespace App\Services\Braintree;

use Braintree\Customer;
use Braintree\PaymentMethod;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Subscription;
use Braintree\Transaction;

/**
 * Interface BraintreeResponse.
 * Used to describe Braintree responses.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 *
 * @property-read Customer|null $customer
 * @property-read PaymentMethod|null $paymentMethod
 * @property-read Subscription|null $subscription
 * @property-read Transaction|null $transaction
 * @property-read string $message
 * @mixin Error
 * @mixin Successful
 */
interface BraintreeResponse
{

}
