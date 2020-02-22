<?php

declare(strict_types=1);

namespace Tests\GraphQL\Mutations;

use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Models\User;
use Tests\GraphQLTestCase;
use const JSON_THROW_ON_ERROR;
use function app;
use function env;
use function json_decode;

/**
 * @testdox Mutation LoginBasic
 */
class LoginBasicTest extends GraphQLTestCase
{
    private const QUERY = '
    mutation ($email: String!, $password: String!) {
        loginBasic(email: $email, password: $password) {
            token
        }
    }
    ';

    /**
     * @testdox returns a JWT on success
     */
    public function testLoginBasicSuccess(): void
    {
        $user = User::random();

        $this->graphQL(
            self::QUERY,
            [
                'email'    => $user->email,
                'password' => env('DB_PASSWORD'),
            ]
        );

        $token = $this->getContent('data.loginBasic.token');

        /** @var Driver $driver */
        $driver  = app()->make(Driver::class);
        $payload = json_decode($driver->unserialize($token)->getPayload(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseOk();
        $this->assertNull($this->getErrors());
        $this->assertNotNull($token);
        $this->assertTrue($driver->verify($token));
        $this->assertEquals($payload->sub, $user->id);
    }

    /**
     * @testdox fails with invalid credentials error on non-existing email
     */
    public function testLoginBasicFailsOnNonExistingEmail(): void
    {
        $this->graphQL(
            self::QUERY,
            [
                'email'    => 'idontexist',
                'password' => env('DB_PASSWORD'),
            ]
        );

        $this->assertResponseOk();
        $this->assertNotNull($this->getErrors());
        $this->assertNotEmpty($this->getContent('errors.0.message'));
    }

    /**
     * @testdox fails with invalid credentials error on invalid password
     */
    public function testLoginBasicFailsOnInvalidPassword(): void
    {
        $this->graphQL(
            self::QUERY,
            [
                'email'    => User::random()->email,
                'password' => env('DB_PASSWORD') . 'invalid',
            ]
        );

        $this->assertResponseOk();
        $this->assertNotNull($this->getErrors());
        $this->assertNotEmpty($this->getContent('errors.0.message'));
    }
}
