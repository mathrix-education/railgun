<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Psr\Log\LogLevel;
use Throwable;
use function trans;

/**
 * Thrown when the user attempts to login with invalid credentials (email/password).
 * This is not considered as a critical error as it can happen under normal conditions. Thus, the level is set to
 * {@see LogLevel::DEBUG}.
 */
class InvalidCredentialsException extends HttpException
{
    protected string $level    = LogLevel::DEBUG;
    protected string $category = 'authentication';

    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        $message ??= trans('auth.invalid_credentials');
        parent::__construct($message, $previous);
    }
}
