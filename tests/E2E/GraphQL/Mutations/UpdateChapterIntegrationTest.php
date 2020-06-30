<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation updateChapter
 */
class UpdateChapterIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * Execute the updateChapter mutation.
     *
     * @param mixed[] $input The UpdateChapter input.
     */
    private function executeUpdateChapter(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateChapter!) {
                updateChapter(input: $input) {
                    id
                    slug
                }
            }
            GRAPHQL,
            ['input' => $input],
        );
    }

    /**
     * @testdox allow to change the slug when there is already another slug in another discipline_id/grade_id space
     */
    public function testChangeToExistingSlugNoConflict(): void
    {
        $theme1 = Theme::random();
        /** @var Chapter $reference */
        $reference = $theme1->chapters()->inRandomOrder()->firstOrFail();

        /** @var Theme $theme2 */
        $theme2  = Theme::query()
            ->inRandomOrder()
            ->where('grade_id', '!=', $theme1->grade_id)
            ->where('discipline_id', '!=', $theme1->discipline_id)
            ->firstOrFail();
        $chapter = factory(Chapter::class)->create(['theme_id' => $theme2->id]);

        $this->withPermissions('chapters:write');
        $this->executeUpdateChapter([
            'id'   => $chapter->id,
            'slug' => $reference->slug,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

    /**
     * @testdox fails to update the slug when there is already another slug in the same discipline_id/grade_id space
     */
    public function testChangeSlugToExistingSlug(): void
    {
        [$reference, $chapter] = factory(Chapter::class, 2)->create([
            'theme_id' => factory(Theme::class)->create()->getKey(),
        ]);

        $this->withPermissions('chapters:write');
        $this->executeUpdateChapter([
            'id'   => $chapter->id,
            'slug' => $reference->slug,
        ]);

        $this->assertResponseOk();
        $this->assertErrorCode('VALIDATION');
        $this->assertErrorCategory('validation');
    }
}
