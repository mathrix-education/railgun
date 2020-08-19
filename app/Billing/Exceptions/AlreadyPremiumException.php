<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Throwable;
use function trans;

/**
 * Thrown when a user tries to subscribe to one the premium plans and is already premium.
 */
class AlreadyPremiumException extends HttpException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(trans('billing.errors.already_premium'), $previous);
    }
}
