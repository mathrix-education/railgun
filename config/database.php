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
    'connections' => [
        /*
        |--------------------------------------------------------------------------
        | Development connection
        |--------------------------------------------------------------------------
        */
        'live' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'api_mathrix_fr',
            'username' => 'api_mathrix_fr',
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
            'database' => 'dev_api_mathrix_fr',
            'username' => 'dev_api_mathrix_fr',
            'password' => 'vM5qJKdng2B3WVBCz5e5',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ],
        'dev_dist' => [
            'driver' => 'mysql',
            'host' => 'dev.api.mathrix.fr',
            'database' => 'dev_api_mathrix_fr',
            'username' => 'dev_api_mathrix_fr',
            'password' => 'vM5qJKdng2B3WVBCz5e5',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Test connection
        |--------------------------------------------------------------------------
        */
        'test' => [
            'driver' => 'mysql',
            'host' => 'mysql',
            'database' => 'test_api_mathrix_fr',
            'username' => 'test_api_mathrix_fr',
            'password' => 'd3SnA4cBxckvHbyNXhAG',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]
    ]
];