<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class UpdateThemeTest extends GraphQLTestCase
{
    public function testUpdateThemeAddChapters(): void
    {
        $this->withPermissions(['themes:write']);
        /** @var Theme $theme */
        $theme = factory(Theme::class)->create();
        [$oldChapter, $newChapter] = factory(Chapter::class, 2)->create();
        $theme->chapters()->attach($oldChapter->id, ['order' => 0]);

        $this->executeUpdateThemeChapter([
            'input' => [
                'id' => $theme->id,
                'chapters' => [
                    'add' => [
                        [
                            'id' => $newChapter->id,
                            'order' => 0,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('chapter_theme', [
            'theme_id' => $theme->id,
            'chapter_id' => $oldChapter->id,
        ],
        );
        $this->seeInDatabase('chapter_theme', [
            'theme_id' => $theme->id,
            'chapter_id' => $newChapter->id,
        ]);
    }

    public function testUpdateThemeReplaceChapters(): void
    {
        $this->withPermissions(['themes:write']);
        /** @var Theme $theme */
        $theme = factory(Theme::class)->create();
        [$oldChapter, $newChapter] = factory(Chapter::class, 2)->create();
        $theme->chapters()->attach($oldChapter->id, ['order' => 0]);

        $this->executeUpdateThemeChapter([
            'input' => [
                'id' => $theme->id,
                'chapters' => [
                    'replace' => [
                        [
                            'id' => $newChapter->id,
                            'order' => 0,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->notSeeInDatabase('chapter_theme', [
            'theme_id' => $theme->id,
            'chapter_id' => $oldChapter->id,
        ],
        );
        $this->seeInDatabase('chapter_theme', [
            'theme_id' => $theme->id,
            'chapter_id' => $newChapter->id,
        ]);
    }

    /**
     * @param mixed[] $input The UpdateTheme parameter.
     */
    private function executeUpdateThemeChapter(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateTheme!) {
                updateTheme(input: $input) {
                    id
                    chapters {
                        id
                        name
                        order
                    }
                }
            }
            GRAPHQL,
            $input);
    }
}
