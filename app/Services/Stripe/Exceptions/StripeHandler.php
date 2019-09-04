<?php

namespace App\Services\Stripe\Exceptions;

use Stripe\Error\Base as StripeBaseException;
use Stripe\Error\Card as StripeCardException;

class StripeHandler
{
    /**
     * @param $exception
     *
     * @throws IncorrectCVCException
     */
    public static function throws(StripeBaseException $exception)
    {
        if ($exception instanceof StripeCardException) {
            $data = $exception->getJsonBody();

            switch ($data["error"]["code"]) {
                case "incorrect_cvc":
                    throw new IncorrectCVCException();
            }
        }
    }
}
