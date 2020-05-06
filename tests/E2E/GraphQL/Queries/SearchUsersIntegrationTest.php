<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function count;
use function mb_strtolower;
use function substr;
use function substr_count;

/**
 * @testdox Query searchUsers
 */
class SearchUsersIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox search users
     */
    public function testSearch(): void
    {
        $this->actingAsNewUser('users:read');

        $query = substr(User::query()->whereNotNull('username')->firstOrFail()->username, 0, 3);

        $this->graphQL(
            <<<'GRAPHQL'
            query($query: String!, $first: Int!) {
                searchUsers(query: $query, first: $first) {
                    data {
                        username
                        email
                        firstname
                        lastname
                        scopes
                        braintree_id
                        stripe_id
                    }
                }
            }
            GRAPHQL,
            [
                'query' => $query,
                'first' => 10,
            ],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertGreaterThanOrEqual(
            count($this->getData('data')),
            substr_count(mb_strtolower($this->response->getContent()), $query),
        );
    }
}
