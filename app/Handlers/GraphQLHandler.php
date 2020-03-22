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
class GraphQLHandler implements ErrorHandler
{
    public static function handle(Error $error, Closure $next): array
    {
        /** @var Handler $handler */
        $handler = app()->make(Handler::class);

        if ($handler instanceof Handler) {
            /** @var Error $transformed */
            $transformed = $handler->transform($error);

            if ($transformed instanceof Error) {
                return $next($transformed);
            }
        }

        return $next($error);
    }
}
