<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Railgun\Exceptions\HttpException;
use Throwable;
use function trans;

/**
 * Thrown when a Stripe {@see Event} is not supported by the webhook handler.
 */
class UnsupportedEventException extends HttpException
{
    protected string $category   = 'billing';
    protected int    $statusCode = 400;

    public function __construct(string $event, ?Throwable $previous = null)
    {
        parent::__construct(trans('billing.errors.unsupported_event', ['eventType' => $event]), $previous);
    }
}
