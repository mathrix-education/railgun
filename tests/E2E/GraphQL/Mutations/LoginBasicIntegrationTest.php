<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use JsonException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function app;
use function env;
use function factory;
use function json_decode;
use function trans;
use const JSON_THROW_ON_ERROR;

/**
 * @testdox [E2] Mutation loginBasic
 */
class LoginBasicIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation ($email: String!, $password: String!) {
        loginBasic(email: $email, password: $password) {
            token
        }
    }
    GRAPHQL;

    /**
     * @testdox returns a JWT on success
     *
     * @throws JsonException
     */
    public function testLoginBasicSuccess(): void
    {
        /** @var User $user */
        $user     = factory(User::class)->create();
        $password = $this->faker->password;
        $user->update(['password' => $password]);

        $this->graphQL(
            self::QUERY,
            [
                'email'    => $user->email,
                'password' => $password,
            ],
        );

        $token = $this->getData('loginBasic.token');

        /** @var Driver $driver */
        $driver  = app()->make(Driver::class);
        $payload = json_decode($driver->unserialize($token)->getPayload(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseOk();
        $this->assertNoErrors();
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
            ],
        );

        $this->assertResponseOk();
        $this->assertEquals(trans('auth.invalid_credentials'), $this->getContent('errors.0.message'));
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
            ],
        );

        $this->assertResponseOk();
        $this->assertEquals(trans('auth.invalid_credentials'), $this->getContent('errors.0.message'));
    }
}
