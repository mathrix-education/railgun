<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Railgun\Models\Grade;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function count;
use function explode;
use function mb_strtolower;
use function substr_count;

class SearchChaptersTest extends GraphQLTestCase
{
    public function testSearch(): void
    {
        /** @var Chapter $reference */
        $reference = Course::query()
            ->where('visible', '=', true)
            ->firstOrFail();

        $query = $this->faker->randomElement(explode('-', $reference->slug));

        $this->graphQL(
            <<<'GRAPHQL'
            query($query: String!, $grade_id: ID!) {
                searchChapters(query: $query, grade_id: $grade_id, first: 100) {
                    data {
                        id
                        name
                        slug
                        theme {
                            grade {
                                name
                            }
                        }
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
