<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Flashcard;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\ClonesGenerator;
use function factory;

class CreateFlashcardTest extends GraphQLTestCase
{
    public function testCreateCloned(): void
    {
        $ref = Flashcard::random();
        ClonesGenerator::cloneChapter($ref->chapter);

        $input = Arr::except(
            factory(Flashcard::class)->raw(['chapter_id' => $ref->chapter_id]),
            ['id', 'created_at', 'updated_at'],
        );

        $this->withPermissions([
            'flashcards:write',
            'flashcards:write:visible',
            'flashcards:write:premium',
            'flashcards:write:protected',
        ]);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateFlashcard!) {
                createFlashcard(input: $input) {
                    id
                }
            }
            GRAPHQL,
            ['input' => $input],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        /** @var Flashcard $flashcard */
        $flashcard = Flashcard::query()
            ->with('clones')
            ->findOrFail($this->getData('id'));

        $this->assertTrue($flashcard->clones->isNotEmpty());
    }
}
