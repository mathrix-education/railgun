<?php

declare(strict_types=1);

namespace Mathrix\Prometheus\Collectors;

use Illuminate\Support\Facades\Queue;

class QueueCollector extends Collector
{
    public function register(): void
    {
        $this->gauge('queue_size', 'Queue sizes');
    }

    public function compute(): void
    {
        $this->gauge('queue_size')->set(Queue::size());
    }
}
