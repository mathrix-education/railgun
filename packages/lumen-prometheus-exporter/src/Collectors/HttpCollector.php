<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Collectors;

class HttpCollector extends Collector
{
    public const HTTP_REQUESTS_TOTAL           = 'http_requests_total';
    public const HTTP_REQUESTS_LATENCY_SECONDS = 'http_requests_latency_seconds';

    public function register(): void
    {
        $this->counter(
            self::HTTP_REQUESTS_TOTAL,
            'It observe requests totals',
            ['method', 'route', 'status'],
        );
        $this->histogram(
            self::HTTP_REQUESTS_LATENCY_SECONDS,
            'The latencies of the HTTP requests per route',
            ['method', 'route', 'status'],
        );
    }
}
