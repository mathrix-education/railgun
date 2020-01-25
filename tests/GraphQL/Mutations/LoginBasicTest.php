<?php

declare(strict_types=1);

namespace Tests\GraphQL\Mutations;

use Tests\GraphQLTestCase;
use function dd;

class LoginBasicTest extends GraphQLTestCase
{
    public function testLoginBasic()
    {
        $query = /** @lang GraphQL */<<<GRAPHQL
        mutation (\$email: String!, \$password: String!) {
            loginBasic(email: \$email, password: \$password) {
                token
            }
        }
        GRAPHQL;

        $this->graphQL($query, [
            'email'    => 'test',
            'password' => 'lo',
        ]);

        dd($this->getErrors());
        $this->assertResponseOk();
    }
}
