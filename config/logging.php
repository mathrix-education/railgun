<?php

use CodeInternetApplications\MonologStackdriver\Laravel\CreateStackdriverLogger;

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
        ],
        "stackdriver" => [
            "driver" => "custom",
            "via" => CreateStackdriverLogger::class,
            "logName" => env("APP_ENV") . ".education-api",
            "loggingClientOptions" => [
                "keyFilePath" => storage_path("keychain/stackdriver.json"),
            ],
            // "loggerOptions" => [],
            // "lineFormat" => "%message%",
            // "entryOptionsWrapper" => "stackdriver",
        ],
    ],
];
