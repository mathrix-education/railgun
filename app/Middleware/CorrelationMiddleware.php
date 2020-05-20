<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function is_string;
use function method_exists;
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
     * @return Response The final response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header(self::HEADER);

        if (!is_string($correlationId) || !uuid_is_valid($correlationId)) {
            $correlationId = uuid_create();
            $request->headers->set(self::HEADER, $correlationId);
        }

        Container::getInstance()->instance(self::ABSTRACT, $correlationId);

        /** @var Response|mixed $response */
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header(self::HEADER, $correlationId);
        }

        return $response;
    }

    /**
     * Drop the correlation id.
     */
    public function terminate(): void
    {
        Container::getInstance()->forgetInstance(self::ABSTRACT);
    }
}
