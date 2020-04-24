<?php

declare(strict_types=1);

use Jose\Component\Signature\Algorithm\EdDSA;
use Jose\Component\Signature\Algorithm\HS256;
use Windy\Guardian\Constants;

return [
    'defaults'    => [
        'authority' => 'login',
        'key'       => 'login',
        'claims'    => 'login',
    ],
    'authorities' => [
        'login' => [
            'key'    => 'login',
            'claims' => 'login',
        ],
        'reset' => [
            'key'    => 'reset',
            'claims' => 'reset',
        ],
    ],
    'keys'        => [
        'login' => [
            'algorithm' => EdDSA::class,
            'curve'     => Constants::CURVE_ED25519,
            'path'      => storage_path('keychain/login.json'),
        ],
        'reset' => [
            'algorithm' => HS256::class,
            'size'      => 256,
            'path'      => storage_path('keychain/reset.json'),
        ],
    ],
    'claims'      => [
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
