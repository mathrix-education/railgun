<?php

declare(strict_types=1);

return [
    'apple'    => [
        'team_id'  => env('APPLE_TEAM_ID'),
        'app_id'   => env('APPLE_APP_ID'),
        'key_file' => env('APPLE_KEY_FILE', storage_path('keychain/apple-signin')),
        'key_id'   => env('APPLE_KEY_ID'),
    ],
    'facebook' => [
        'app_id'     => env('FACEBOOK_APP_ID'),
        'app_secret' => env('FACEBOOK_APP_SECRET'),
    ],
    'google'   => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
    ],
];
