<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Psr\Log\LogLevel;
use Throwable;
use function trans;

/**
 * Thrown when the provide authentication token is invalid.
 * This is not considered as a critical error as it can happen under normal conditions, for example when the user got
 * tries to use an outdated JWT. Thus, the level is set to {@see LogLevel::DEBUG}.
 */
class InvalidAuthTokenException extends HttpException
{
    protected string $level    = LogLevel::DEBUG;
    protected string $category = 'authentication';

    public function __construct(string $source = 'API', ?Throwable $previous = null)
    {
        parent::__construct(trans('auth.invalid_token', ['source' => $source]), $previous);
    }
}
