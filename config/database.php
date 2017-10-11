<?php
/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
|
| Manage and define the databases connections
|
*/
return [
    'default' => env('DB_CONNECTION'),
    'migrations' => 'migrations',
    'connections' => [
        /*
        |--------------------------------------------------------------------------
        | Development connection
        |--------------------------------------------------------------------------
        */
        'live' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'mathrix_fr',
            'username' => 'mathrix_fr',
            'password' => 't3QczMqKDBwKqRA83bKK',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Development connection
        |--------------------------------------------------------------------------
        */
        'dev' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'mathrix_fr_dev',
            'username' => 'mathrix_fr_dev',
            'password' => 'vM5qJKdng2B3WVBCz5e5',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'options' => [
                PDO::ATTR_PERSISTENT => env('DB_PERSISTENT', false),
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Tests connection
        |--------------------------------------------------------------------------
        */
        'tests' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'mathrix_fr_tests',
            'username' => 'mathrix_fr_tests',
            'password' => 'uFFgY3pt2TEVc9NRAgac',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Pipelines connection
        |--------------------------------------------------------------------------
        */
        'pipelines' => [
            'driver' => 'mysql',
            'host' => 'mysql',
            'database' => 'mathrix_fr_pipelines',
            'username' => 'root',
            'password' => 'd3SnA4cBxckvHbyNXhAG',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]
    ]
];