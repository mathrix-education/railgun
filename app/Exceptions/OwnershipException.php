<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Throwable;
use function trans;

class OwnershipException extends HttpException
{
    protected string $category = 'auth';

    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(trans('auth.ownership'), $previous);
    }
}
