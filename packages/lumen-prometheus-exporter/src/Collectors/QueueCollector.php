<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Collectors;

class QueueCollector extends Collector
{
    public function register(): void
    {
        $this->gauge('queue_size', 'Queue sizes', ['queue_name']);
    }
}
