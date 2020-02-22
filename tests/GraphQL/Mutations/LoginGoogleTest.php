<?php

declare(strict_types=1);

namespace Tests\GraphQL\Mutations;

use Google_Client;
use Mathrix\Lumen\JWT\Drivers\Driver;

class LoginGoogleTest
{
    private const QUERY = '
    mutation ($accessToken: String!) {
        loginWithGoogle(accessToken: $accessToken) {
            token
        }
    }
    ';

    private Google_Client $client;
    private Driver $driver;

    public function testLoginGoogleSuccess(): void
    {
    }

    public function testLoginGoogleFail(): void
    {
    }

    public function testLoginGoogleRegister(): void
    {
    }
}
