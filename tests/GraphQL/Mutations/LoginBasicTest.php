<?php

declare(strict_types=1);

namespace Tests\GraphQL\Mutations;

use Tests\GraphQLTestCase;

/**
 *
 */
class LoginBasicTest extends GraphQLTestCase
{
    public function testLoginBasic()
    {
        $this->graphQL(<<<GRAPHQL
        mutation (\$email: String!, \$password: String!) {
            loginBasic(email: \$email, password: \$password) {
                token
            }
        }
        GRAPHQL, [
            'email'    => 'test',
            'password' => 'lo',
        ]);

        dd($this->getErrors());
        $this->assertResponseOk();
    }
}
