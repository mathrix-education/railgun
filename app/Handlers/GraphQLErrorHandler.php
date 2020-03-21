<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use GraphQL\Error\Error;
use Nuwave\Lighthouse\Execution\ErrorHandler;
use Nuwave\Lighthouse\Execution\ExtensionErrorHandler;
use function app;

/**
 * Intercepts exceptions before passing them to the next error handler.
 *
 * @see ExtensionErrorHandler
 */
class GraphQLErrorHandler implements ErrorHandler
{
    public static function handle(Error $error, Closure $next): array
    {
        /** @var ExceptionConverter $converter */
        $converter = app()->make(ExceptionConverter::class);

        $underlyingException = $error->getPrevious();
        $convertedException  = $converter->transform($underlyingException);

        if ($underlyingException !== $convertedException) {
            $error = new Error(
                $convertedException->getMessage(),
                $error->nodes,
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $convertedException
            );
        }

        return $next($error);
    }
}
