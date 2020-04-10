<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Course;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function count;
use function explode;
use function mb_strtolower;
use function substr_count;

/**
 * @testdox Query searchCourses
 */
class SearchCoursesIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox search chapters
     */
    public function testSearch(): void
    {
        $this->actingAsNewUser(['premium_bypass' => true]);
        $query = explode('-', Course::random()->slug)[0];

        $this->graphQL(
            <<<'GRAPHQL'
            query($query: String!) {
                searchCourses(query: $query, first: 100) {
                    data {
                        name
                        slug
                        seo_title
                        seo_slug
                        seo_keywords
                        seo_description
                        content
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
