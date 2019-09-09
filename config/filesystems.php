<?php

declare(strict_types=1);

return [
    'default' => env('FILESYSTEM_DRIVER', 'gcs'),
    'disks' => [
        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', null),
            'visibility' => 'public',
        ],
    ],
];
