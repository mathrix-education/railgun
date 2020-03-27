<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Course;
use Railgun\Models\Note;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox [E2E] Mutation deleteNote
 */
class DeleteNoteIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox soft deletes a note
     */
    public function testNoteIsSoftDeleted(): void
    {
        /** @var Course $course */
        $course = Course::query()->has('notes')->inRandomOrder()->firstOrFail();
        /** @var Note $note */
        $note = $course->notes->first();

        $this->actingAsNewUser(['scopes' => ['notes:delete']]);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($noteId: ID!) {
                deleteNote(id: $noteId) {
                    id
                    notable {
                        ... on Course {
                            id
                        }
                    }
                }
            }
            GRAPHQL,
            ['noteId' => $note->id]
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($note->id, $this->getData('deleteNote.id'));
        $this->assertEquals($course->id, $this->getData('deleteNote.notable.id'));

        $note->refresh();
        $this->assertNotNull($note->deleted_at);

        $course = $course->fresh();
        $this->assertEmpty($course->notes);
    }
}
