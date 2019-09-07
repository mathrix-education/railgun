<?php

namespace App\Services\Stripe\Exceptions;

use Illuminate\Support\Facades\App;
use Mathrix\Lumen\Zero\Exceptions\Http\Http422UnprocessableEntityException;
use Stripe\Error\Card as StripeCardException;
use Throwable;

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
        parent::__construct([
            "decline_code" => $exception->declineCode
        ], trans("stripe.card.declined"), $previous);
    }
}
