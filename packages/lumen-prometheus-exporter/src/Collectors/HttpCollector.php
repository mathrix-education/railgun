<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Collectors;

class HttpCollector extends Collector
{
    public function register(): void
    {
        $this->histogram(
            'http_request_seconds',
            'It observe requests duration',
            ['method', 'route', 'status'],
        );
    }
}
