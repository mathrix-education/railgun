<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mathrix\Prometheus\Collectors\HttpCollector;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\Exception\StorageException;
use RedisException;
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

            try {
                $this->collector->counter(HttpCollector::HTTP_REQUESTS_TOTAL)->inc($route);
                $this->collector->histogram(HttpCollector::HTTP_REQUESTS_LATENCY_SECONDS)->observe($latency, $route);
            } catch (StorageException | RedisException $exception) {
                Log::error($exception->getMessage(), ['exception' => $exception]);
            }
        }

        return $response;
    }
}
