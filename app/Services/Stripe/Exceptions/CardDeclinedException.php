<?php

namespace App\Services\Stripe\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http422UnprocessableEntityException;
use Stripe\Error\Card as StripeCardException;
use Throwable;

/**
 * Class CardDeclinedException.
 * Thrown when a card has been declined.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class CardDeclinedException extends Http422UnprocessableEntityException
{
    /**
     * CardDeclinedException constructor.
     *
     * @param StripeCardException $exception
     * @param Throwable|null $previous
     */
    public function __construct(StripeCardException $exception, Throwable $previous = null)
    {
        parent::__construct(
            ["decline_code" => $exception->getDeclineCode()],
            trans("stripe.card.declined"),
            $previous
        );
    }
}
