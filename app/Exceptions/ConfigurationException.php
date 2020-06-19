<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

/**
 * Thrown when there is a invalid Laravel configuration.
 */
class ConfigurationException extends HttpException
{
    protected int $statusCode = 500;
}
