<?php

declare(strict_types=1);

use Mathrix\Prometheus\Collectors\HttpCollector;
use Mathrix\Prometheus\Collectors\QueueCollector;
use Railgun\Collectors\UserCollector;

return [
    'collectors' => [
        HttpCollector::class,
        QueueCollector::class,
        UserCollector::class,
    ],
];
