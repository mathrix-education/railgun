<?php

declare(strict_types=1);

use Mathrix\Prometheus\Collectors\HttpCollector;
use Mathrix\Prometheus\Collectors\QueueCollector;
use Railgun\Collectors\GraphQLCollector;
use Railgun\Collectors\PHPPMCollector;

return [
    'namespace'  => env('APP_NAME'),
    'redis'      => ['connection' => 'prometheus'],
    'collectors' => [
        HttpCollector::class,
        QueueCollector::class,
        PHPPMCollector::class,
        GraphQLCollector::class,
    ],
];
