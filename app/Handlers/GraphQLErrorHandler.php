<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use GraphQL\Error\Error;
use Illuminate\Validation\ValidationException;
use Nuwave\Lighthouse\Execution\ErrorHandler;
use Nuwave\Lighthouse\Execution\ExtensionErrorHandler;
use Railgun\Exceptions\Validation;
use Throwable;

/**
 * Intercepts exceptions before passing them to the next error handler.
 *
 * @see ExtensionErrorHandler
 */
class GraphQLErrorHandler implements ErrorHandler
{
    public static function handle(Error $error, Closure $next): array
    {
        $underlyingException = $error->getPrevious();

        if ($underlyingException instanceof ValidationException) {
            $error = self::swapException($error, Validation::from($underlyingException));
        }

        return $next($error);
    }

    /**
     * Swap the underlying exception of a {@see Error} by recreating a new instance.
     *
     * @param Error     $original     The original GraphQL error.
     * @param Throwable $newException The new exception.
     *
     * @return Error
     */
    private static function swapException(Error $original, Throwable $newException): Error
    {
        return new Error(
            $newException->getMessage(),
            $original->nodes,
            $original->getSource(),
            $original->getPositions(),
            $original->getPath(),
            $newException
        );
    }
}
