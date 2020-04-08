<?php

declare(strict_types=1);

return [
    'url'    => '/healthz',
    'action' => 'Mathrix\\Healthz\\Controllers\\HealthzController@healthz',
    'checks' => [
        'Mathrix\\Healthz\\Checks\\DatabaseCheck' => ['details' => false],
        'Mathrix\\Healthz\\Checks\\RedisCheck' => [],
    ],
];
