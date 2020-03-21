<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Illuminate\Validation\ValidationException;
use Railgun\Billing\Exceptions\Card;
use Railgun\Exceptions\Validation;
use Stripe\Exception\CardException;
use Throwable;

class ExceptionConverter
{
    public function transform(Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return Validation::from($exception);
        }

        if ($exception instanceof CardException) {
            return Card::from($exception);
        }

        return $exception;
    }
}
