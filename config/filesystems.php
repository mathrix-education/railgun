<?php
return [
    "default" => env("FILESYSTEM_DRIVER", "cdn"),
    "disks" => [
        "cdn" => [
            "driver" => "gcs",
            "project_id" => "mathrix-education",
            "key_file" => storage_path("keychain/gcs.json"),
            "bucket" => env("GOOGLE_CLOUD_STORAGE_BUCKET"),
            "path_prefix" => env("GOOGLE_CLOUD_STORAGE_PATH_PREFIX", null),
            "visibility" => "public"
        ]
    ],
];
