<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Throwable;
use function trans;

/**
 * Thrown when the validated cart is empty.
 */
class EmptyCartException extends HttpException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(trans('cart.errors.empty'), $previous);
    }
}
