<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Exception\StorageException;
use Railgun\Collectors\GraphQLCollector;
use RedisException;
use Symfony\Component\HttpFoundation\Response;
use function microtime;

/**
 * Observe the GraphQL operations metrics.
 */
class GraphQLOperationMiddleware
{
    private GraphQLCollector $collector;
    private float            $latency;

    public function __construct(GraphQLCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return Response The final response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->has('operationName')) {
            return $next($request);
        }

        $start         = microtime(true);
        $response      = $next($request);
        $this->latency = microtime(true) - $start;

        return $response;
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     *
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     */
    public function terminate(Request $request): void
    {
        if (!$request->has('operationName')) {
            return;
        }

        $operation = $request->json('operationName');

        try {
            $this->collector
                ->counter(GraphQLCollector::GRAPHQL_OPERATIONS_TOTAL)
                ->inc([$operation]);
            $this->collector
                ->histogram(GraphQLCollector::GRAPHQL_OPERATIONS_LATENCY_SECONDS)
                ->observe($this->latency, [$operation]);
        } catch (StorageException | RedisException $exception) {
            // Silently log the exception but don't throw as the response was already built
            Log::error($exception->getMessage(), ['exception' => $exception]);
        }

    }
}
