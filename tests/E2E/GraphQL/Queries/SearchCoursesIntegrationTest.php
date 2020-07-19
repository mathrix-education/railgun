<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Str;
use Railgun\Models\Course;
use Railgun\Models\Grade;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function explode;
use function json_encode;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

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
        $this->actingAs(['premium_bypass' => true]);

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

        foreach ($this->getData('data') as $result) {
            // Ensure that all returned results contains the query
            $options = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

            $normalized = (string)Str::of(json_encode($result, $options, 512))
                ->ascii()
                ->lower();

            $this->assertStringContainsString($query, $normalized);
        }
    }
}
