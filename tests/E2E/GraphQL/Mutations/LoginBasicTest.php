<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use JsonException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Windy\Guardian\Guardian;
use function env;
use function factory;
use function json_decode;
use function trans;
use const JSON_THROW_ON_ERROR;

class LoginBasicTest extends GraphQLTestCase
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

        $authority = Guardian::get('login');
        $payload   = json_decode($authority->unserialize($token)->getPayload(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertNotNull($token);
        $this->assertTrue($authority->verify($token));
        $this->assertEquals($payload->sub, $user->id);
    }

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
