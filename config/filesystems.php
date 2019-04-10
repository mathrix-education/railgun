<?php
return [
    "default" => env("FILESYSTEM_DRIVER", "cdn"),
    "disks" => [
        "cdn" => [
            "driver" => "gcs",
            "project_id" => "mathrix-education",
            "key_file" => storage_path("google-cloud-storage/key.json"),
            "bucket" => env("GOOGLE_CLOUD_STORAGE_BUCKET", "your-bucket"),
            "path_prefix" => env("GOOGLE_CLOUD_STORAGE_PATH_PREFIX", null),
            "visibility" => "private"
        ]
    ],
];
