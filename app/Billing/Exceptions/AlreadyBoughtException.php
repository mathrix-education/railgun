<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to buy a product he already owns.
 */
class AlreadyBoughtException extends HttpException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    /**
     * @param string[]       $productNames The products name.
     * @param Throwable|null $previous     The previous exception, if any.
     */
    public function __construct(array $productNames, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('billing.errors.already_bought', ['name' => implode(', ', $productNames)]),
            $previous,
        );
    }
}
