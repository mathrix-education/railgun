<?php

declare(strict_types=1);

use Jose\Component\Signature\Algorithm\EdDSA;
use Mathrix\Lumen\JWT\Drivers\EdDSADriver;
use Railgun\Models\User;

return [
    'auth'     => [
        'driver_name' => 'jwt',
        'user_model'  => User::class,
    ],
    'key'      => 'eddsa',
    'keys'     => [
        'eddsa' => [
            'algorithm' => EdDSA::class,
            'curve'     => EdDSADriver::CURVE_ED25519,
            'path'      => storage_path('keychain/jwt_auth.json'),
        ],
    ],
    'payload'  => 'default',
    'payloads' => [
        'default' => [
            'iss' => 'Railgun API',
            'aud' => 'Mathrix Education Angular',
            'exp' => '+3 months',
            'nbf' => 'now',
            'iat' => 'now',
            'jid' => 'uuid',
        ],
    ],
];
