<?php

namespace App\Services\Stripe\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http422UnprocessableEntityException;
use Stripe\Error\Card as StripeCardException;
use Throwable;

/**
 * Class IncorrectCVCException.
 * Thrown when a card CVC is incorrect.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class IncorrectCVCException extends Http422UnprocessableEntityException
{
    protected $error = "incorrect_cvc";


    /**
     * IncorrectCVCException constructor.
     *
     * @param StripeCardException $exception
     * @param Throwable|null $previous
     */
    public function __construct(StripeCardException $exception, Throwable $previous = null)
    {
        parent::__construct(
            ["decline_code" => $exception->getDeclineCode()],
            trans("stripe.card.incorrect_cvc"),
            $previous
        );
    }
}
