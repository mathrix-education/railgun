<?php

declare(strict_types=1);

namespace Tests\GraphQL\Mutations;

use App\Models\User;
use Tests\GraphQLTestCase;
use function env;

class LoginBasicTest extends GraphQLTestCase
{
    public function testLoginBasic()
    {
        $query = <<<GRAPHQL
        mutation (\$email: String!, \$password: String!) {
            loginBasic(email: \$email, password: \$password) {
                token
            }
        }
        GRAPHQL;

        $this->graphQL($query, [
            'email'    => User::random()->email,
            'password' => env('DB_PASSWORD'),
        ]);

        $this->assertResponseOk();
    }
}
