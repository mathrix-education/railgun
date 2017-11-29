<?php

return [
    "default" => env("FILESYSTEM_DRIVER", "cdn"),

    "disks" => [
        "storage" => [
            "driver" => "local",
            "root" => storage_path(),
            "visibility" => "public",
        ],
        "cdn" => [
            "driver" => "local",
            "root" => storage_path("cdn"),
            "url" => env("APP_CDN"),
            "visibility" => "public",
        ]
    ]
];