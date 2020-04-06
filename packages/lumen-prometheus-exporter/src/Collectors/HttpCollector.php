<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Collectors;

use Prometheus\Histogram;
use function array_map;

class HttpCollector extends Collector
{
    public function register(): void
    {
        $this->counter(
            'http_request_total',
            'It observe requests totals',
            ['method', 'route', 'status'],
        );
        $this->histogram(
            'http_requests_latency_milliseconds',
            'The latencies of the HTTP requests per route',
            ['method', 'route', 'status'],
            array_map(static fn($v) => $v * 1000, Histogram::getDefaultBuckets()),
        );
    }
}
