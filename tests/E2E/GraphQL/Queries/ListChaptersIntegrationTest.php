<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Chapter;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function array_map;
use function count;
use function sort;

/**
 * @testdox Query listChapters
 */
class ListChaptersIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @param mixed[] $variables The query variables.
     */
    private function executeListChapter(array $variables): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query(
                $gradeId: ID
                $disciplineId: ID
                $gradeSlug: String
                $disciplineSlug: String
            ) {
                listChapters(
                    gradeId: $gradeId
                    disciplineId: $disciplineId
                    gradeSlug: $gradeSlug
                    disciplineSlug: $disciplineSlug
                ) {
                    id
                    slug
                    order
                    discipline {
                        id
                        slug
                    }
                    grade {
                        id
                        slug
                    }
                }
            }
            GRAPHQL,
            $variables,
        );
    }

    /**
     * @testdox lists chapters using grade and discipline ids.
     */
    public function testWithIds(): void
    {
        $chapter = Chapter::random();

        $this->executeListChapter([
            'gradeId'      => $chapter->grade_id,
            'disciplineId' => $chapter->discipline_id,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $chapters = $this->getData('listChapters');
        $this->assertGreaterThan(0, count($chapters));
        foreach ($chapters as $chapterData) {
            $this->assertEquals($chapter->grade_id, $chapterData->grade->id);
            $this->assertEquals($chapter->discipline_id, $chapterData->discipline->id);
        }

        // Assert chapters ordered
        $actualOrder   = array_map(static fn($c) => $c->order, $chapters);
        $expectedOrder = $actualOrder;
        sort($expectedOrder);
        $this->assertEquals($expectedOrder, $actualOrder);
    }

    /**
     * @testdox lists chapters using grade and discipline slugs.
     */
    public function testWithSlugs(): void
    {
        $chapter = Chapter::random();

        $this->executeListChapter([
            'gradeSlug'      => $chapter->grade->slug,
            'disciplineSlug' => $chapter->discipline->slug,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $chapters = $this->getData('listChapters');
        $this->assertGreaterThan(0, count($chapters));
        foreach ($chapters as $chapterData) {
            $this->assertEquals($chapter->grade->slug, $chapterData->grade->slug);
            $this->assertEquals($chapter->discipline->slug, $chapterData->discipline->slug);
        }
    }
}
