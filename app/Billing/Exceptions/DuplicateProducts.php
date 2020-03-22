<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\GraphQLException;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when the cart contains duplicated items.
 */
class DuplicateProducts extends GraphQLException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    public function __construct(array $ids, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('cart.errors.duplicate', ['ids' => implode(', ', $ids)]),
            $previous
        );
    }
}
