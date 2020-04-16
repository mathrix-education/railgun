<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use Illuminate\Database\QueryException as IlluminateQueryException;
use Railgun\Exceptions\ForeignKeyConstraintException;
use Railgun\Exceptions\QueryException;
use Throwable;

class SQLTransformer implements TransformsExceptions
{
    /**
     * Transform a exception into another.
     *
     * @param Throwable $original The original exception.
     * @param Closure   $next     The next exception transformer.
     *
     * @return Throwable The transformed exception.
     */
    public function transform(Throwable $original, Closure $next): Throwable
    {
        if ($original instanceof IlluminateQueryException) {
            return $this->transform(new QueryException(null, $original), $next);
        }

        if ($original instanceof QueryException && isset($original->extensions()['errorInfo'][1])) {
            switch ($original->extensions()['errorInfo'][1]) {
                case 1451:
                    return $next(ForeignKeyConstraintException::from($original));
            }
        }

        return $next($original);
    }
}
