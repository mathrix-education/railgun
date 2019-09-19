<?php

declare(strict_types=1);

use App\Services\Sentry\RequestBodyIntegration;

return [
    'dsn' => env('SENTRY_DSN'),
    'environment' => env('APP_ENV'),
    'release' => env('APP_BUILD'),
    'breadcrumbs' => ['sql_bindings' => true],
    'integrations' => [new RequestBodyIntegration()],
];
