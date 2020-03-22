<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Psr\Log\LogLevel;
use Throwable;
use function trans;

/**
 * Thrown an authentication is invalid.
 * This is not considered as a critical error, thus the log level is set to DEBUG.
 */
class InvalidAuthToken extends HttpException
{
    protected string $level    = LogLevel::DEBUG;
    protected string $category = 'authentication';

    public function __construct(string $source = 'API', ?Throwable $previous = null)
    {
        parent::__construct(trans('auth.invalid_token', ['source' => $source]), $previous);
    }
}
