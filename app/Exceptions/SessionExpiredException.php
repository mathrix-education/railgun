<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Throwable;

class SessionExpiredException extends HttpException
{
    protected string $category = 'authentication';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(trans('auth.expired_session'), $previous);
    }
}
