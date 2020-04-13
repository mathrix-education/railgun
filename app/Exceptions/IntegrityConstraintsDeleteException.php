<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

class IntegrityConstraintsDeleteException extends HttpException
{
    protected string $category = 'database';
}
