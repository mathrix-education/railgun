<?php
return [
    "default" => env("LOG_CHANNEL", "stack"),

    "channels" => [
        "stack" => [
            "driver" => "stack",
            "channels" => "daily",
        ],

        "daily" => [
            "driver" => "daily",
            "path" => storage_path("logs/daily.log"),
            "level" => "debug",
            "days" => 7,
        ]
    ],
];
