<?php

namespace App\Exceptions;

use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
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
     * @param BraintreeResponse $error
     * @param User|null $user The user (will unlock it)
     *
     * @throws GatewayRejectionException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    public static function report($error, User $user = null): void
    {
        if ($user instanceof User) {
            $user->unlock();
        }

        if ($error->transaction instanceof Transaction) {
            self::wrapper("transaction", $error);
        } else if ($error->subscription instanceof Subscription) {
            self::wrapper("subscription", $error);
        } else {
            throw new UnknownBraintreeException($error->jsonSerialize());
        }
    }


    /**
     * @param string $type
     * @param Error $error
     *
     * @throws GatewayRejectionException
     * @throws ProcessorDeclinedException
     */
    private static function wrapper(string $type, $error): void
    {
        /** @var Subscription|Transaction $object */
        $object = $error->{$type};

        if ($object->status === "gateway_rejected") {
            // Handle gateway rejection
            $exceptionPayload = [
                "reason" => $object->gatewayRejectionReason
            ];

            throw new GatewayRejectionException($exceptionPayload, $error->message);
        } else {
            // Handle processor rejection
            $exceptionPayload = [
                "processor" => [
                    "code" => (int)$object->processorResponseCode,
                    "text" => $object->processorResponseText,
                    "type" => $object->processorResponseType
                ]
            ];

            throw new ProcessorDeclinedException($exceptionPayload, $object->processorResponseText);
        }
    }
}
