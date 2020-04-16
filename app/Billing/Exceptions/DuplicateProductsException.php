<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when the cart contains duplicated items.
 */
class DuplicateProductsException extends HttpException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    /**
     * @param string[]       $names    The duplicated product names.
     * @param Throwable|null $previous The previous exception, if any.
     */
    public function __construct(array $names, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('cart.errors.duplicate', ['names' => implode(', ', $names)]),
            $previous,
        );
    }
}
