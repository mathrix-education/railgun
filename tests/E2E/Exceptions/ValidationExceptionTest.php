<?php

declare(strict_types=1);

namespace Tests\E2E\Exceptions;

use Tests\E2E\GraphQL\GraphQLTestCase;

class ValidationExceptionTest extends GraphQLTestCase
{
    /**
     * Since the Schema mock does not work, we have to use the createUser mutation.
     */
    public function testValidationFails(): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateUser!) {
                createUser(
                    input: $input
                ) {
                    id
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'username' => 'foo',
                    'email'    => 'invalid-email',
                    'password' => '123456',
                ],
            ],
        );

        $this->assertResponseOk();
        $this->assertErrorCode('VALIDATION');
        $this->assertErrorCategory('validation');
    }
}
