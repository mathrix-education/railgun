<?php
return [
    "default" => env("FILESYSTEM_DRIVER", "cdn.mathrix.fr"),
    "disks" => [
        "cdn.mathrix.fr" => [
            "driver" => "gcs",
            "project_id" => env("GOOGLE_CLOUD_PROJECT_ID"),
            "bucket" => env("GOOGLE_CLOUD_STORAGE_BUCKET"),
            "path_prefix" => env("GOOGLE_CLOUD_STORAGE_PATH_PREFIX", null),
            "visibility" => "public"
        ]
    ],
];
