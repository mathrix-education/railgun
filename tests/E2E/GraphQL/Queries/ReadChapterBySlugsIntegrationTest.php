<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Railgun\Models\Discipline;
use Railgun\Models\Grade;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox [E2E] Query readChapterBySlugs
 */
class ReadChapterBySlugsIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox returns two different chapters when duplicates exists.
     */
    public function testReadExistingDuplicates(): void
    {
        $slug = $this->faker->slug;

        /**
         * @var Grade $grade1
         * @var Grade $grade2
         */
        [$grade1, $grade2] = Grade::query()
            ->inRandomOrder()
            ->limit(2)
            ->get();

        $discipline = Discipline::random();

        factory(Chapter::class)->create([
            'slug'          => $slug,
            'discipline_id' => $discipline->id,
            'grade_id'      => $grade1->id,
        ]);

        factory(Chapter::class)->create([
            'slug'          => $slug,
            'discipline_id' => $discipline->id,
            'grade_id'      => $grade2->id,
        ]);

        $this->graphQL(
            <<<'GRAPHQL'
            fragment Slugs on Chapter {
                id
                slug
                discipline {
                    slug
                }
                grade {
                    slug
                }
            }

            query($chapterSlug: String!, $disciplineSlug: String!, $gradeSlug1: String!, $gradeSlug2: String!) {
                chapter1: readChapterBySlugs(
                    chapterSlug: $chapterSlug
                    disciplineSlug: $disciplineSlug
                    gradeSlug: $gradeSlug1
                ) {
                    ...Slugs
                }
                
                chapter2: readChapterBySlugs(
                    chapterSlug: $chapterSlug
                    disciplineSlug: $disciplineSlug
                    gradeSlug: $gradeSlug2
                ) {
                    ...Slugs
                }
            }
            GRAPHQL,
            [
                'chapterSlug'    => $slug,
                'disciplineSlug' => $discipline->slug,
                'gradeSlug1'     => $grade1->slug,
                'gradeSlug2'     => $grade2->slug,
            ],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertNotEquals($this->getData('chapter1.id'), $this->getData('chapter2.id'));
        $this->assertNotEquals($this->getData('chapter1.grade.slug'), $this->getData('chapter2.grade.slug'));
    }
}
