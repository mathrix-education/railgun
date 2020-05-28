<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Chapter;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox Query readChapter
 */
class ReadChapterIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @param mixed[] $variables
     */
    private function executeReadChapter(array $variables): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query(
                $id: ID
                $chapterSlug: String
                $gradeSlug: String
                $disciplineSlug: String
            ) {
                readChapter(
                    id: $id
                    chapterSlug: $chapterSlug
                    gradeSlug: $gradeSlug
                    disciplineSlug: $disciplineSlug
                ) {
                    id
                    slug
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
     * @testdox reads a chapter using its id
     */
    public function testWithId(): void
    {
        $chapter = Chapter::random();

        $this->executeReadChapter(['id' => $chapter->id]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($chapter->id, $this->getData('id'));
    }

    /**
     * @testdox reads a chapter using its slug, grade slug and discipline slug
     */
    public function testWithSlugs(): void
    {
        $chapter = Chapter::random();

        $this->executeReadChapter([
            'chapterSlug'    => $chapter->slug,
            'gradeSlug'      => $chapter->grade->slug,
            'disciplineSlug' => $chapter->discipline->slug,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($chapter->id, $this->getData('id'));
        $this->assertEquals($chapter->slug, $this->getData('slug'));
        $this->assertEquals($chapter->grade->slug, $this->getData('grade.slug'));
        $this->assertEquals($chapter->discipline->slug, $this->getData('discipline.slug'));
    }
}
