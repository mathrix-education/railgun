<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mathrix\Prometheus\Collectors\HttpCollector;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Symfony\Component\HttpFoundation\Response;
use function microtime;

class PrometheusMiddleware
{
    private HttpCollector $collector;

    public function __construct(HttpCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return mixed The final response.
     *
     * @throws MetricNotFoundException
     * @throws MetricsRegistrationException
     */
    public function handle(Request $request, Closure $next)
    {
        $start    = microtime(true);
        $response = $next($request);
        $latency  = ($start - microtime(true)) * 1000; // in ms

        if ($response instanceof Response) {
            $route = [
                $request->method(),
                $request->getPathInfo() ?: '/',
                $response->getStatusCode(),
            ];

            $this->collector->counter('http_request_total')->inc($route);
            $this->collector->histogram('http_requests_latency_milliseconds')->observe($latency, $route);
        }

        return $response;
    }
}
