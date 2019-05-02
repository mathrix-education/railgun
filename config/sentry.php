<?php

return [
    "dsn" => env("SENTRY_DSN"),
    "environment" => env("APP_ENV"),
    "release" => env("APP_BUILD"),
    "breadcrumbs" => [
        "sql_bindings" => true,
    ]
];
