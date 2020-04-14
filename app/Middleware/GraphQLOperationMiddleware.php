<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Railgun\Collectors\GraphQLCollector;
use Symfony\Component\HttpFoundation\Response;
use function microtime;

/**
 * Observe the GraphQL operations metrics.
 */
class GraphQLOperationMiddleware
{
    private GraphQLCollector $collector;

    public function __construct(GraphQLCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return Response The final response.
     *
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->has('operationId')) {
            return $next($request);
        }

        $start    = microtime();
        $response = $next($request);
        $latency  = microtime() - $start;

        $operationId = $request->json('operationId');
        $this->collector
            ->counter(GraphQLCollector::GRAPHQL_OPERATIONS_TOTAL)->inc([$operationId]);
        $this->collector
            ->histogram(GraphQLCollector::GRAPHQL_OPERATIONS_LATENCY_SECONDS)
            ->observe($latency, [$operationId]);

        return $response;
    }
}
