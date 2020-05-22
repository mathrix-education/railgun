<?php

declare(strict_types=1);

use Mathrix\Healthz\Checks\DatabaseCheck;
use Mathrix\Healthz\Checks\RedisCheck;
use Mathrix\Healthz\Checks\StorageCheck;
use Railgun\Checks\MailjetCheck;
use Railgun\Checks\StripeCheck;

return [
    'checks' => [
        [
            'class'   => DatabaseCheck::class,
            'details' => false,
        ],
        [
            'class'   => RedisCheck::class,
            'details' => false,
        ],
        [
            'class'   => StorageCheck::class,
            'details' => false,
        ],
        [
            'class'   => MailjetCheck::class,
            'details' => false,
        ],
        [
            'class'   => StripeCheck::class,
            'details' => false,
        ],
    ],
];
