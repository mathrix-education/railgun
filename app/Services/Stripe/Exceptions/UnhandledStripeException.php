<?php

namespace App\Services\Stripe\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http500InternalServerErrorException;
use Stripe\Error\Base as StripeBaseException;
use Throwable;

/**
 * Class UnknownStripeException.
 * Thrown when an unhandled Stripe Exception.
 *
 * @see StripeHandler
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class UnhandledStripeException extends Http500InternalServerErrorException
{
    public function __construct(StripeBaseException $exception, Throwable $previous = null)
    {
        parent::__construct(json_encode($exception->getJsonBody()), $exception->getMessage(), $previous);
    }
}
