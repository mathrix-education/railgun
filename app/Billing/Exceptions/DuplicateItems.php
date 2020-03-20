<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Throwable;
use function implode;
use function trans;

/**
 * Thrown when the cart contains duplicated items.
 */
class DuplicateItems extends InvalidCart
{
    public function __construct(
        array $ids,
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        parent::__construct(
            trans('cart.errors.duplicate', ['ids' => implode(', ', $ids)]),
            $previous,
            $code,
            $headers
        );
    }
}
