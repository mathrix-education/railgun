<?php
return [
    'dsn' => env('SENTRY_DSN'),
    // capture release as git sha
    'release' => build_date(),
    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,
    // Capture default user context
    'user_context' => false,
];
