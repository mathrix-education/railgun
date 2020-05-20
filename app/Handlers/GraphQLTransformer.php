<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use GraphQL\Error\Error as GraphQLError;
use Throwable;

class GraphQLTransformer implements TransformsExceptions
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
        $embedded = $original->getPrevious();

        if (!$original instanceof GraphQLError || !$embedded instanceof Throwable) {
            return $next($original);
        }

        $converted = $this->transform($embedded, $next);

        if ($converted !== null) {
            // Exception need to be swapped
            return $next(
                new GraphQLError(
                    $converted->getMessage(),
                    $original->nodes,
                    $original->getSource(),
                    $original->getPositions(),
                    $original->getPath(),
                    $converted,
                ),
            );
        }

        return $next($original); // @codeCoverageIgnore
    }
}
