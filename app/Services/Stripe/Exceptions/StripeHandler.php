<?php

declare(strict_types=1);

namespace App\Services\Stripe\Exceptions;

use Stripe\Error\Card as StripeCardException;
use Throwable;

/**
 * Handle Stripe exceptions.
 */
class StripeHandler
{
    /**
     * @param Throwable $exception
     *
     * @throws CardDeclined
     * @throws IncorrectCVC
     */
    public static function handle(Throwable $exception): void
    {
        if (! ($exception instanceof StripeCardException)) {
            return;
        }

        $data = $exception->getJsonBody();

        switch ($data['error']['code']) {
            case 'incorrect_cvc':
                throw new IncorrectCVC($exception);
            case 'card_declined':
                throw new CardDeclined($exception);
        }
    }
}
