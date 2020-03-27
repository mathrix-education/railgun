<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function app;
use function factory;

/**
 * @testdox [E2] Query me
 */
class MeIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox returns the user id using a true, non-mocked JWT
     */
    public function testBearerAuth(): void
    {
        $user = factory(User::class)->create();
        /** @var Driver $driver */
        $driver = app()->make(Driver::class);
        $bearer = $driver->sign($user);

        $this->postGraphQL(
            [
                'query' => <<<GRAPHQL
                query {
                    me {
                        id
                    }
                }
                GRAPHQL,
            ],
            ['Authorization' => "Bearer $bearer"]
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals((string)$user->id, $this->getData('me.id'));
    }
}
