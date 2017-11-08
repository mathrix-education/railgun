<?php

return [
    "default" => env("FILESYSTEM_DRIVER", "cdn"),

    "disks" => [
        "cdn" => [
            "driver" => "local",
            "root" => storage_path("cdn"),
            "url" => env("APP_URL") . "/storage",
            "visibility" => "public",
        ]
    ]
];