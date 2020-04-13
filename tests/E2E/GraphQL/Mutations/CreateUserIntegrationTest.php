<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation createUser
 */
class CreateUserIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox fails when using a duplicate email.
     */
    public function testExistingEmail(): void
    {
        $user = factory(User::class)->create(['username' => $this->faker->userName]);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateUser!) {
                createUser(input: $input) {
                    id
                    username
                    email
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'username' => $user->username,
                    'email'    => $user->email,
                    'password' => $this->faker->password,
                ],
            ],
        );

        $this->assertResponseOk();
        $this->assertError(null, 'validation');
        $this->assertCount(1, DB::table('users')->where([
            'username' => $user->username,
            'email'    => $user->email,
        ])->get());
    }
}
