<?php

namespace App\Services\Stripe\Exceptions;

use Exception;
use Stripe\Error\Card as StripeCardException;

/**
 * Class StripeHandler.
 * Handle Stripe exceptions.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class StripeHandler
{
    /**
     * @param Exception $exception
     *
     * @throws CardDeclinedException
     * @throws IncorrectCVCException
     */
    public static function handle(Exception $exception)
    {
        if ($exception instanceof StripeCardException) {
            $data = $exception->getJsonBody();

            switch ($data["error"]["code"]) {
                case "incorrect_cvc":
                    throw new IncorrectCVCException($exception);
                case "card_declined":
                    throw new CardDeclinedException($exception);
            }
        }
    }
}
