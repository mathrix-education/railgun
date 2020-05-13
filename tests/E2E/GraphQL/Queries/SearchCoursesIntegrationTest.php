<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Course;
use Railgun\Models\Grade;
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

        /** @var Course $reference */
        $reference = Course::query()
            ->where('visible', '=', true)
            ->firstOrFail();

        $query = $this->faker->randomElement(explode('-', $reference->slug));

        $this->graphQL(
            <<<'GRAPHQL'
            query($query: String!, $grade_id: ID!) {
                searchCourses(query: $query, grade_id: $grade_id, first: 100) {
                    data {
                        name
                        slug
                        seo_title
                        seo_keywords
                        seo_description
                        content
                    }
                }
            }
            GRAPHQL,
            ['query' => $query, 'grade_id' => Grade::random()->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertGreaterThanOrEqual(
            count($this->getData('data')),
            substr_count(mb_strtolower($this->response->getContent()), $query),
        );
    }
}
