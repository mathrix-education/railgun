<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function array_merge;
use function factory;

class CreateChapterTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    public function testNonExistingSlug(): void
    {
        $this->withPermissions(['chapters:write', 'chapters:write:visible']);
        $this->executeCreateChapter(Arr::only(
            factory(Chapter::class)->raw(),
            ['name', 'order', 'visible', 'slug', 'theme_id'],
        ));

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('chapters', ['id' => $this->getData('id')]);
    }

    /**
     * Execute the createChapter mutation.
     *
     * @param mixed[] $input The CreateChapter input.
     */
    private function executeCreateChapter(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateChapter!) {
                createChapter(input: $input) {
                    id
                }
            }
            GRAPHQL,
            ['input' => $input],
        );
    }

    public function testExistingSlug(): void
    {
        $reference = Chapter::random();
        $input     = array_merge(
            Arr::only(factory(Chapter::class)->raw(), ['name', 'order', 'visible']),
            Arr::only($reference->toArray(), ['slug']),
        );

        $this->withPermissions(['chapters:write', 'chapters:write:visible']);
        $this->executeCreateChapter($input);

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}
