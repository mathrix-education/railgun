<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function app;
use function is_string;
use function uuid_create;
use function uuid_is_valid;

/**
 * Set the X-Correlation-ID of a request.
 */
class CorrelationMiddleware
{
    public const ABSTRACT = 'correlation.id';
    private const HEADER  = 'X-Correlation-ID';

    /**
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return mixed The final response.
     */
    public function handle(Request $request, Closure $next)
    {
        $correlationId = $request->header(self::HEADER);

        if (!is_string($correlationId) || !uuid_is_valid($correlationId)) {
            $correlationId = uuid_create();
            $request->headers->set(self::HEADER, $correlationId);
        }

        app()->instance(self::ABSTRACT, $correlationId);

        /** @var Response|mixed $response */
        $response = $next($request);

        if ($response instanceof Response) {
            $response->header(self::HEADER, $correlationId);
        }

        return $response;
    }
}
