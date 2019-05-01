<?php

namespace App\Services\Braintree\Exceptions;

use Braintree\Result\Error;
use Braintree\Subscription;
use Braintree\Transaction;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class BraintreeErrorHandler.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class BraintreeErrorHandler
{
    /**
     * Handle an unsuccessful Braintree request result.
     *
     * @param Error $error
     *
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     * @throws DuplicatePaymentMethodNonceException
     * @throws Http400BadRequestException
     */
    public static function report($error)
    {
        if ($error->transaction instanceof Transaction) {
            self::wrapper("transaction", $error);
        } else if ($error->subscription instanceof Subscription) {
            self::wrapper("subscription", $error);
        } else if ($error->message === "Cannot use a payment_method_nonce more than once.") {
            throw new DuplicatePaymentMethodNonceException([], $error->message);
        } else {
            throw new Http400BadRequestException($error->jsonSerialize(), "Unknown Braintreee exception.");
        }
    }


    /**
     * @param string $type
     * @param Error $error
     *
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     */
    private static function wrapper(string $type, $error)
    {
        /** @var Subscription|Transaction $object */
        $object = $error->{$type};

        if ($object->status === "gateway_rejected") {
            // Handle gateway rejection
            $exceptionPayload = [
                "reason" => $object->gatewayRejectionReason
            ];

            throw new BraintreeGatewayException($exceptionPayload, $error->message);
        } else {
            // Handle processor rejection
            $exceptionPayload = [
                "processor" => [
                    "code" => (int)$object->processorResponseCode,
                    "text" => $object->processorResponseText,
                    "type" => $object->processorResponseType
                ]
            ];

            throw new BraintreeProcessorException($exceptionPayload, $object->processorResponseText);
        }
    }
}
