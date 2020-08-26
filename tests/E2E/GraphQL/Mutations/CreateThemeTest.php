<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class CreateThemeTest extends GraphQLTestCase
{
    /**
     * @param mixed[] $input The CreateTheme GraphQL Input.
     */
    public function execute(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateTheme!) {
                createTheme(input: $input) {
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

    public function testCreateWithChapters(): void
    {
        /** @var Collection|Chapter[] $chapters */
        $chapters = Chapter::query()
            ->inRandomOrder()
            ->has('themes')
            ->limit(3)
            ->get();

        $data = Arr::except(factory(Theme::class)->raw([
            'grade_id'      => $chapters[0]->themes[0]->grade_id,
            'discipline_id' => $chapters[0]->themes[0]->discipline_id,
        ]), ['created_at', 'updated_at']);

        $data['chapters'] = [
            'add' => $chapters->pluck('id')->toArray(),
        ];

        $this->withPermissions(['themes:write', 'themes:write:visible']);

        $this->execute($data);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $chapters->pluck('id')->each(function (string $chapterId, int $order): void {
            $this->seeInDatabase('chapter_theme', [
                'theme_id'   => $this->getData('id'),
                'chapter_id' => $chapterId,
                'order'      => $order,
            ]);
        });
    }
}
