<?php

namespace App\Services\Braintree\Exceptions;

use Braintree\Result\Error;
use Braintree\Subscription;
use Braintree\Transaction;

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
     */
    public static function report(Error $error)
    {
        if ($error->transaction instanceof Transaction) {
            self::wrapper("transaction", $error);
        } else if ($error->subscription instanceof Subscription) {
            self::wrapper("subscription", $error);
        }
    }


    /**
     * @param string $type
     * @param Error $error
     *
     * @throws BraintreeGatewayException
     * @throws BraintreeProcessorException
     */
    private static function wrapper(string $type, Error $error)
    {
        /** @var Subscription|Transaction $object */
        $object = $error->{$type};

        if ($object->status === "gateway_rejected") {
            // Handle dateway rejection
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
