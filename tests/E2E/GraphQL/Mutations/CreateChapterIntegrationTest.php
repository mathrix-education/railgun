<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Chapter;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function array_merge;
use function factory;

/**
 * @testdox Mutation createChapter
 */
class CreateChapterIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

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

    /**
     * @testdox creates a new chapter.
     */
    public function testNonExistingSlug(): void
    {
        $this->withPermissions(['chapters:write', 'chapters:write:visible']);
        $this->executeCreateChapter(Arr::only(
            factory(Chapter::class)->raw(),
            ['name', 'order', 'visible', 'slug', 'grade_id', 'discipline_id'],
        ));

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('chapters', ['id' => $this->getData('id')]);
    }

    /**
     * @testdox fails to create a chapter with an existing slug/grade_id/discipline_id.
     */
    public function testExistingSlug(): void
    {
        $reference = Chapter::random();
        $input     = array_merge(
            Arr::only(factory(Chapter::class)->raw(), ['name', 'order', 'visible']),
            Arr::only($reference->toArray(), ['slug', 'grade_id', 'discipline_id']),
        );

        $this->withPermissions(['chapters:write', 'chapters:write:visible']);
        $this->executeCreateChapter($input);

        $this->assertResponseOk();
        $this->assertErrorCode('VALIDATION');
        $this->assertErrorCategory('validation');
    }
}
