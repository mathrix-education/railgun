<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Database\QueryException as IlluminateQueryException;
use Throwable;
use function assert;

/**
 * Wrapper of an {@see IlluminateQueryException}.
 */
class QueryException extends HttpException
{
    protected string $category = 'database';

    public function __construct(?string $message, Throwable $previous)
    {
        if ($previous instanceof IlluminateQueryException) {
            parent::__construct($message ?? $previous->errorInfo[2], $previous);
        } else {
            parent::__construct($message ?? $previous->getMessage(), $previous);
        }
    }

    /**
     * @return mixed[]
     */
    public function extensions(): array
    {
        $previous = $this->getPrevious();

        if (!$previous instanceof IlluminateQueryException) {
            return [];
        }

        assert($previous instanceof IlluminateQueryException);

        return [
            'errorInfo' => $previous->errorInfo,
            'query'     => $previous->getSql(),
            'bindings'  => $previous->getBindings(),
        ];
    }
}
