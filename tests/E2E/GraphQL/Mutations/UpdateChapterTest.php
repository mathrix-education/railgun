<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class UpdateChapterTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    public function testChangeToExistingSlugNoConflict(): void
    {
        /** @var Theme $theme1 */
        $theme1 = Theme::query()->has('chapters')->inRandomOrder()->first();
        /** @var Chapter $reference */
        $reference = $theme1->chapters()->inRandomOrder()->firstOrFail();

        /** @var Theme $theme2 */
        $theme2 = Theme::query()
            ->inRandomOrder()
            ->where('grade_id', '!=', $theme1->grade_id)
            ->where('discipline_id', '!=', $theme1->discipline_id)
            ->first();

        /** @var Chapter $chapter */
        $chapter = factory(Chapter::class)->create();
        $chapter->themes()->attach($theme2->id, ['order' => 0]);

        $this->withPermissions('chapters:write');
        $this->executeUpdateChapter([
            'id'   => $chapter->id,
            'slug' => $reference->slug,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }

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
}

