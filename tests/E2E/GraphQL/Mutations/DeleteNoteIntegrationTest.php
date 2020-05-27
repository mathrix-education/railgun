<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Course;
use Railgun\Models\Note;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox Mutation deleteNote
 */
class DeleteNoteIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox soft deletes a note
     */
    public function testNoteIsSoftDeleted(): void
    {
        /** @var Course $course */
        $course = factory(Course::class)->create();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'notable_type' => 'course',
            'notable_id'   => $course->id,
            'deleted_at'   => null,
        ]);

        $this->withPermissions('notes:delete');

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
            ['noteId' => $note->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($note->id, $this->getData('id'));
        $this->assertEquals($course->id, $this->getData('notable.id'));

        $note->refresh();
        $this->assertNotNull($note->deleted_at);

        $course = $course->fresh();
        $this->assertEmpty($course->notes);
    }
}
