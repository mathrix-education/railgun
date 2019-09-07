<?php

namespace App\Services\Stripe\Exceptions;

use Stripe\Error\Base as StripeBaseException;
use Stripe\Error\Card as StripeCardException;

class StripeHandler
{
    /**
     * @param StripeBaseException $exception
     *
     * @throws CardDeclinedException
     * @throws IncorrectCVCException
     * @throws UnhandledStripeException
     */
    public static function rethrow(StripeBaseException $exception)
    {
        if ($exception instanceof StripeCardException) {
            $data = $exception->getJsonBody();

            switch ($data["error"]["code"]) {
                case "incorrect_cvc":
                    throw new IncorrectCVCException();
                case "card_declined":
                    throw new CardDeclinedException($exception);
            }
        }

        throw new UnhandledStripeException($exception); // Fallback exception
    }
}
