<?php

declare(strict_types=1);

return [
    'mailjet' => [
        'key'     => env('MAILJET_KEY'),
        'secret'  => env('MAILJET_SECRET'),
        'call'    => env('MAILJET_CALL', true),
        'options' => ['version' => 'v3.1'],
    ],
];
