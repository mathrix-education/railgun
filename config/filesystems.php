<?php

declare(strict_types=1);

return [
    'default' => env('FILESYSTEM_DRIVER', 'cdn'),
    'disks'   => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('public'),
        ],
        'cdn'   => [
            'driver'      => 'gcs',
            'project_id'  => env('GOOGLE_CLOUD_PROJECT_ID'),
            /**
             * Hacky way to using the local Google Cloud Credentials.
             *
             * @link https://github.com/Superbalist/laravel-google-cloud-storage/issues/60#issuecomment-604438443
             * @link https://github.com/Superbalist/laravel-google-cloud-storage/pull/61
             */
            'key_file'    => '',
            'bucket'      => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
            'visibility'  => 'public',
        ],
    ],
];
