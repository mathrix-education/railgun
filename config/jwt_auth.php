<?php

declare(strict_types=1);

use App\Models\User;
use Jose\Component\Signature\Algorithm\ES512;

return [
    'key'        => [
        'path'  => storage_path('keychain/jwt_auth.json'),
        'type'  => 'ecdsa',
        'ecdsa' => ['curve' => 'P-521'],
    ],
    'algorithm'  => ES512::class,
    'user_model' => User::class,
    'expiration' => [
        'value' => 3,
        'unit'  => 'month',
    ],
    'driver'     => 'jwt-auth',
    'guard'      => 'api',
    'jwt'        => [
        'iss' => 'Mathrix Education API',
        'aud' => 'Mathrix Education Angular',
    ],
];
