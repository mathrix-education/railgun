<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Database\Query\Builder;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

class ListLatestViewedChaptersIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    query {
        listLatestViewedChapters {
            id
            name
        }
    }
    GRAPHQL;

    public function testList()
    {
        /** @var User $user */
        $user = User::query()
            ->inRandomOrder()
            ->has('interactions')
            ->firstOrFail();
        $this->actingAs($user);
        $this->graphQL(self::QUERY);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}
