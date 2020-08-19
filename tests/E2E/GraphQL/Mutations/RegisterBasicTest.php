<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Hash;
use JsonException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Windy\Guardian\Guardian;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class RegisterBasicTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
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

        $authority = Guardian::get('login');
        $this->assertTrue($authority->verify($token));
        $payload = json_decode($authority->unserialize($token)->getPayload(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals($payload->sub, $user->id);
    }
}
