<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use App\Services\Braintree\BraintreeResponse;
use Braintree\Result\Error;
use Braintree\Subscription;
use Braintree\Transaction;

class BraintreeErrorHandler
{
    /**
     * Handle an unsuccessful Braintree request result.
     *
     * @param User|null $user The user (will unlock it)
     *
     * @throws GatewayRejectionException
     * @throws ProcessorDeclinedException
     * @throws UnknownBraintreeException
     */
    public static function report(BraintreeResponse $error, ?User $user = null) : void
    {
        if ($user instanceof User) {
            $user->unlock();
        }

        if ($error->transaction instanceof Transaction) {
            self::wrapper('transaction', $error);
        } elseif ($error->subscription instanceof Subscription) {
            self::wrapper('subscription', $error);
        } else {
            throw new UnknownBraintreeException($error->jsonSerialize());
        }
    }

    /**
     * @throws GatewayRejectionException
     * @throws ProcessorDeclinedException
     */
    private static function wrapper(string $type, Error $error) : void
    {
        /** @var Subscription|Transaction $object */
        $object = $error->{$type};

        if ($object->status === 'gateway_rejected') {
            // Handle gateway rejection
            $exceptionPayload = [
                'reason' => $object->gatewayRejectionReason,
            ];

            throw new GatewayRejectionException($exceptionPayload, $error->message);
        }

        // Handle processor rejection
        $exceptionPayload = [
            'processor' => [
                'code' => (int) $object->processorResponseCode,
                'text' => $object->processorResponseText,
                'type' => $object->processorResponseType,
            ],
        ];

        throw new ProcessorDeclinedException($exceptionPayload, $object->processorResponseText);
    }
}
