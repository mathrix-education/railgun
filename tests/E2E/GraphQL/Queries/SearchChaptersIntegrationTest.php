<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Chapter;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function count;
use function explode;
use function mb_strtolower;
use function substr_count;

/**
 * @testdox Query searchChapters
 */
class SearchChaptersIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox search chapters
     */
    public function testSearch(): void
    {
        $query = explode('-', Chapter::random()->slug)[0];

        $this->graphQL(
            <<<'GRAPHQL'
            query($query: String!) {
                searchChapters(query: $query, first: 100) {
                    data {
                        name
                        slug
                    }
                }
            }
            GRAPHQL,
            ['query' => $query],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertGreaterThanOrEqual(
            count($this->getData('data')),
            substr_count(mb_strtolower($this->response->getContent()), $query),
        );
    }
}
