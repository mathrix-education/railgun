<?php

return [
    "default" => env("FILESYSTEM_DRIVER", "cdn"),

    "disks" => [
        "apidoc" => [
            "driver" => "local",
            "root" => app()->basePath("apidoc"),
            "visibility" => "public",
        ],
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
        ],
        "drive" => [
            "driver" => "google",
            "clientId" => env("GOOGLE_DRIVE_CLIENT_ID"),
            "clientSecret" => env("GOOGLE_DRIVE_CLIENT_SECRET"),
            "refreshToken" => env("GOOGLE_DRIVE_REFRESH_TOKEN"),
            "folderId" => env("GOOGLE_DRIVE_FOLDER_ID"),
        ],
    ],
];
