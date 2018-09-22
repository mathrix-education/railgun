<?php

return [
    "mailgun" => [
        "domain" => env("MAILGUN_DOMAIN"),
        "secret" => env("MAILGUN_SECRET"),
    ],
    "stripe" => [
        "model" => App\Models\User::class,
        "key" => env("STRIPE_KEY"),
        "secret" => env("STRIPE_SECRET"),
    ],
];
