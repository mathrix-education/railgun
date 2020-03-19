<?php

declare(strict_types=1);

use Jose\Component\Signature\Algorithm\EdDSA;
use Jose\Component\Signature\Algorithm\HS256;
use Mathrix\Lumen\JWT\Drivers\EdDSADriver;
use Railgun\Models\User;

return [
    'auth'     => [
        'driver_name' => 'jwt',
        'user_model'  => User::class,
    ],
    'key'      => 'login',
    'keys'     => [
        'login' => [
            'algorithm' => EdDSA::class,
            'curve'     => EdDSADriver::CURVE_ED25519,
            'path'      => storage_path('keychain/login.json'),
        ],
        'reset' => [
            'algorithm' => HS256::class,
            'size'      => 256,
            'path'      => storage_path('keychain/reset.json'),
        ],
    ],
    'payload'  => 'login',
    'payloads' => [
        'login' => [
            'iss' => 'Railgun API',
            'exp' => '+3 months',
            'nbf' => 'now',
            'iat' => 'now',
            'jid' => 'uuid',
        ],
        'reset' => [
            'iss' => 'Railgun API',
            'exp' => '+7 days',
            'nbf' => 'now',
            'iat' => 'now',
            'jid' => 'uuid',
        ],
    ],
];
