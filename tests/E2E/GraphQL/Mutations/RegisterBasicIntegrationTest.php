<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Hash;
use JsonException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function app;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * @testdox Mutation registerBasic
 */
class RegisterBasicIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox registers a new user.
     *
     * @throws JsonException
     */
    public function testRegister(): void
    {
        $email    = $this->faker->uuid . '@mathrix.fr';
        $password = $this->faker->password;

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($email: String!, $password: String!) {
                registerBasic(email: $email, password: $password) {
                    token
                }
            }
            GRAPHQL,
            ['email' => $email, 'password' => $password],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        $token = $this->getData('token');
        $this->assertNotNull($token);

        /** @var User $user */
        $user = User::query()->where('email', '=', $email)->firstOrFail();
        $this->assertTrue(Hash::check($password, $user->password));

        /** @var Driver $driver */
        $driver = app()->make(Driver::class);
        $this->assertTrue($driver->verify($token));
        $payload = json_decode($driver->unserialize($token)->getPayload(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($payload->sub, $user->id);
    }
}
