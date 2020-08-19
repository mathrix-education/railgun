<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Exception;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function count;
use function mb_strtolower;
use function random_int;
use function strlen;
use function substr;
use function substr_count;

class SearchUsersTest extends GraphQLTestCase
{
    /**
     * @throws Exception
     */
    public function testSearch(): void
    {
        $this->withPermissions('users:read');

        $username = User::query()->whereNotNull('username')->firstOrFail()->username;
        $query    = mb_strtolower(substr($username, random_int(0, strlen($username) - 4), 3));

        $this->graphQL(
            <<<'GRAPHQL'
            query($query: String!, $first: Int!) {
                searchUsers(query: $query, first: $first) {
                    data {
                        username
                        email
                        firstname
                        lastname
                        permissions
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
