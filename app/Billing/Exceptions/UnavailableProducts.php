<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to buy unavailable products.
 */
class UnavailableProducts extends HttpException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    public function __construct(array $ids, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('cart.errors.unavailable', ['ids' => implode(', ', $ids)]),
            $previous
        );
    }
}
