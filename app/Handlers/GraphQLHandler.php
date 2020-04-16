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
    /**
     * @param Error   $original The current GraphQL {@see Error}
     * @param Closure $next     The next handler callback.
     *
     * @return mixed[]
     */
    public static function handle(Error $original, Closure $next): array
    {
        /** @var ExceptionPipeline $pipeline */
        $pipeline = app()->make(ExceptionPipeline::class);

        /** @var Error $transformed */
        $transformed = $pipeline->transform($original);

        if ($transformed instanceof Error) {
            return $next($transformed);
        }

        return $next($original); // @codeCoverageIgnore
    }
}
