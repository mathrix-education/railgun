<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Support\Silicate;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox Mutation createNote
 */
class CreateNoteIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox creates a note in the database
     */
    public function testNoteIsCreated(): void
    {
        $user   = $this->actingAsNewUser(['scopes' => ['notes:create']]);
        $course = Course::random();

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($content: Silicate!, $notable_type: NotableType!, $notable_id: ID!) {
                createNote(
                    content: $content
                    notable_type: $notable_type
                    notable_id: $notable_id
                ) {
                    id
                    notable {
                        ... on Course {
                            id
                        }
                    }
                }
            }
            GRAPHQL,
            [
                'content'      => Silicate::wall(),
                'notable_type' => 'COURSE',
                'notable_id'   => $course->id,
            ],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('notes', [
            'user_id'      => $user->id,
            'notable_type' => 'course',
            'notable_id'   => $course->id,
        ]);
        $this->assertEquals($course->id, $this->getData('createNote.notable.id'));
    }
}
