<?php

declare(strict_types=1);

use Railgun\Models\User;

return [
    'defaults'  => [
        'guard'     => 'guardian',
        'passwords' => 'users',
    ],
    'guards'    => [
        'guardian' => [
            'driver'    => 'guardian',
            'provider'  => 'users',
            'authority' => 'login',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => User::class,
        ],
    ],
];
