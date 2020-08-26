<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Chapter;
use Tests\E2E\GraphQL\GraphQLTestCase;

class ReadChapterTest extends GraphQLTestCase
{
    public function testWithId(): void
    {
        $chapter = Chapter::random();

        $this->executeReadChapter(['id' => $chapter->id]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($chapter->id, $this->getData('id'));
    }

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
                    themes {
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
            }
            GRAPHQL,
            $variables,
        );
    }

    public function testWithSlugs(): void
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()
            ->has('themes')
            ->inRandomOrder()
            ->firstOrFail();

        $this->executeReadChapter([
            'chapterSlug'    => $chapter->slug,
            'gradeSlug'      => $chapter->themes[0]->grade->slug,
            'disciplineSlug' => $chapter->themes[0]->discipline->slug,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $this->assertEquals($chapter->id, $this->getData('id'));
        $this->assertEquals($chapter->slug, $this->getData('slug'));
        $this->assertEquals($chapter->themes[0]->grade->slug, $this->getData('themes.0.grade.slug'));
        $this->assertEquals($chapter->themes[0]->discipline->slug, $this->getData('themes.0.discipline.slug'));
    }

    public function testAvailable(): void
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()->whereNotNull('price')->firstOrFail();

        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readChapter(id: $id) {
                    id
                    available
                }
            }
            GRAPHQL,
            ['id' => $chapter->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertTrue($this->getData('available'));
    }
}
