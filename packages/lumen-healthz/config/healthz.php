<?php

declare(strict_types=1);

use Mathrix\Healthz\Checks\DatabaseCheck;
use Mathrix\Healthz\Checks\RedisCheck;
use Mathrix\Healthz\Controllers\HealthzController;

return [
    'url'    => '/healthz',
    'action' => HealthzController::class . '@healthz',
    'checks' => [
        [
            'class'   => DatabaseCheck::class,
            'details' => false,
        ],
        [
            'class' => RedisCheck::class,
        ],
    ],
];
