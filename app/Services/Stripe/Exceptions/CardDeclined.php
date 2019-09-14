<?php

declare(strict_types=1);

namespace App\Services\Stripe\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http422UnprocessableEntityException;
use Stripe\Error\Card as StripeCardException;
use Throwable;
use function trans;

/**
 * Thrown when a card has been declined.
 */
class CardDeclined extends Http422UnprocessableEntityException
{
    public function __construct(StripeCardException $exception, ?Throwable $previous = null)
    {
        parent::__construct(
            ['decline_code' => $exception->getDeclineCode()],
            trans('stripe.card.declined'),
            $previous
        );
    }
}
