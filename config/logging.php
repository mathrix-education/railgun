<?php

return [
    "default" => env("LOG_CHANNEL", "syslog"),
    "channels" => [
        "stack" => [
            "driver" => "stack",
            "channels" => ["syslog", "daily"],
        ],
        "syslog" => [
            "driver" => "syslog",
            "level" => "debug",
        ],
        "daily" => [
            "driver" => "daily",
            "path" => storage_path("logs/daily.log"),
            "level" => "debug",
            "days" => 7,
        ]
    ],
];
