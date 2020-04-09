<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Course;
use Railgun\Models\Interaction;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function trans;

/**
 * @testdox [E2E] Query readCourse
 */
class ReadCourseIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox returns a non-null interactable when it exists (#165)
     *
     * @ticket #165
     */
    public function testInteractableIsNotNull(): void
    {
        Queue::fake();

        // Create an interaction
        $user        = $this->actingAsNewUser();
        $course      = Course::random();
        $interaction = new Interaction([
            'user_id'           => $user->id,
            'interactable_type' => 'course',
            'interactable_id'   => $course->id,
            'comprehension'     => 'clear',
        ]);
        $interaction->save();

        // Read the associated interactable
        $this->graphQL(
            <<<'QUERY'
            query($courseId: ID!) {
                readCourse(id: $courseId) {
                    interaction {
                        interactable_type
                        interactable_id
                    }
                }
            }
            QUERY,
            ['courseId' => $course->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals('COURSE', $this->getData('readCourse.interaction.interactable_type'));
        $this->assertEquals($course->id, $this->getData('readCourse.interaction.interactable_id'));
    }

    /**
     * @testdox fails to read the course notes
     */
    public function testReadNotesMissingScope(): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readCourse(id: $id) {
                    notes {
                        id
                    }
                }
            }
            GRAPHQL,
            ['id' => Course::random()->id],
        );

        $this->assertResponseOk();
        $this->assertError(trans('auth.missing_scope', ['scope' => 'notes:read']));
    }

    /**
     * @testdox returns an empty content when querying a locked course
     */
    public function testPremiumLocked(): void
    {
        /** @var Course $course */
        $course = Course::query()->inRandomOrder()->where('premium', '=', true)->firstOrFail();

        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readCourse(id: $id) {
                    id
                    content
                }
            }
            GRAPHQL,
            ['id' => $course->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($course->id, $this->getData('readCourse.id'));
        $this->assertEmpty($this->getData('readCourse.content'));
    }
}
