<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Railgun\Exceptions\HttpException;
use Throwable;
use function trans;

/**
 * Thrown when the validated cart is empty.
 */
class EmptyCartException extends HttpException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(trans('cart.errors.empty'), $previous);
    }
}
