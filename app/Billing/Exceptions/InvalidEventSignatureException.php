<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Railgun\Exceptions\HttpException;
use Throwable;
use function trans;

/**
 * Thrown when a event signature is invalid.
 * Wrapper for {@see SignatureVerificationException}.
 */
class InvalidEventSignatureException extends HttpException
{
    protected string $category = 'billing';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(trans('billing.errors.invalid_signature'), $previous);

        $this->setStatusCode(400);
    }
}
