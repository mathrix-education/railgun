<?php
return [
    "default" => env("FILESYSTEM_DRIVER", "cdn"),
    "disks" => [
        "cdn" => [
            "driver" => "gcs",
            "project_id" => "mathrix-education",
            "key_file" => storage_path("keychain/google-cloud.json"),
            "bucket" => env("GOOGLE_CLOUD_STORAGE_BUCKET"),
            "path_prefix" => env("GOOGLE_CLOUD_STORAGE_PATH_PREFIX", null),
            "visibility" => "public"
        ]
    ],
];
