<?php

declare(strict_types=1);

return [
    'release'          => env('APP_NAME') . '-' . env('APP_BUILD', 'unknown'),
    'dsn'              => 'https://d906ba960896426cbdebb9781fdbecfc@sentry.mathrix.fr/3',
    'send_default_pii' => false, // we define the user context ourselves
];
