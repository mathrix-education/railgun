<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Str;
use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function count;
use function explode;
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
            query($query: String!) {
                searchChapters(query: $query, first: 100) {
                    data {
                        id
                        name
                        slug
                        themes {
                            grade {
                                name
                            }
                        }
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
            substr_count(Str::lower($this->response->getContent()), $query),
        );
    }
}
