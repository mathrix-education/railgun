<?php

declare(strict_types=1);

return [
    'default' => env('FILESYSTEM_DRIVER', 'gcs'),
    'disks'   => [
        'gcs' => [
            'driver'      => 'gcs',
            'project_id'  => env('GOOGLE_CLOUD_PROJECT_ID'),
            'key_file'    => env('GOOGLE_CLOUD_KEY_FILE', null) !== null
                ? storage_path(env('GOOGLE_CLOUD_KEY_FILE'))
                : null,
            'bucket'      => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
            'visibility'  => 'public',
        ],
    ],
];
