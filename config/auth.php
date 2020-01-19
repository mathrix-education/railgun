<?php

declare(strict_types=1);

use App\Models\User;

return [
    'defaults'  => [
        'guard'     => 'api',
        'passwords' => 'users',
    ],
    'guards'    => [
        'api' => [
            'driver'   => 'jwt-auth',
            'provider' => 'users',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => User::class,
        ],
    ],
];
