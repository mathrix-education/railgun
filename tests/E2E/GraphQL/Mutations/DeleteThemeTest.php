<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class DeleteThemeTest extends GraphQLTestCase
{
    public function testDeleteTheme(): void
    {
        $this->withPermissions(['themes:delete']);
        /** @var Theme $theme */
        $theme = factory(Theme::class)->create();
        /** @var Chapter $oldChapter */
        $oldChapter = factory(Chapter::class)->create();
        $theme->chapters()->attach($oldChapter->id, ['order' => 0]);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($themeId: ID!) {
                deleteTheme(id: $themeId) {
                    id
                }
            }
            GRAPHQL,
            ['themeId' => $theme->id]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->notSeeInDatabase('themes', [
            'id' => $theme->id,
        ]);
        $this->notSeeInDatabase('chapter_theme', [
            'theme_id' => $theme->id,
        ]);
    }
}
