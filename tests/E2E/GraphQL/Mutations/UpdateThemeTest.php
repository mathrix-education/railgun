<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class UpdateThemeTest extends GraphQLTestCase
{
    /**
     * @param mixed[] $input The UpdateTheme GraphQL input.
     */
    public function execute(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateTheme!) {
                updateTheme(input: $input) {
                    id
                    name
                    chapters {
                        id
                        name
                    }
                }
            }
            GRAPHQL,
            ['input' => $input],
        );
    }

    public function testUpdateWithChapters(): void
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()->has('themes')->inRandomOrder()->firstOrFail();
        $theme   = factory(Theme::class)->create([
            'grade_id'      => $chapter->themes[0]->grade_id,
            'discipline_id' => $chapter->themes[0]->discipline_id,
        ]);

        $chapters = Chapter::query()
            ->whereHas('themes', static function (Builder $query) use ($theme): void {
                $query->where('discipline_id', '=', $theme->discipline_id);
            })
            ->where('id', '!=', $chapter->id)
            ->inRandomOrder()
            ->pluck('id');

        $existing = $chapters->random(3);
        $add      = $chapters->random(3);
        $delete   = $existing->random(1);
        $expected = $existing->merge($add)->reject(static fn($id) => $delete->contains($id));

        $theme->chapters()->sync($theme->parseChapterLinks($existing->toArray()));

        $this->withPermissions(['themes:write', 'themes:write:visible']);
        $this->execute([
            'id'       => $theme->id,
            'chapters' => [
                'add'    => $add->toArray(),
                'delete' => $delete->toArray(),
            ],
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $expected->each(function (int $id) use ($theme): void {
            $this->seeInDatabase('chapter_theme', ['theme_id' => $theme->id, 'chapter_id' => $id]);
        });
        $delete->each(function (int $id) use ($theme): void {
            $this->notSeeInDatabase('chapter_theme', ['theme_id' => $theme->id, 'chapter_id' => $id]);
        });
    }
}
