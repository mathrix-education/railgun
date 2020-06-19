<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

class ImmutableException extends HttpException
{
    /** @var int $statusCode The response status code. */
    protected int $statusCode = 500;
}
