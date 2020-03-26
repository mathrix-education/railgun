<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Comment;
use Railgun\Models\Course;
use Railgun\Models\Interaction;
use Railgun\Notifications\CommentReplyNotification;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function trans;

/**
 * @testdox [E2E] Query readCourse
 * @coversNothing
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
        $this->graphQL(<<<'QUERY'
        query($courseId: ID!) {
            readCourse(id: $courseId) {
                interaction {
                    interactable_type
                    interactable_id
                }
            }
        }
        QUERY, ['courseId' => $course->id]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals('COURSE', $this->getData('readCourse.interaction.interactable_type'));
        $this->assertEquals($course->id, $this->getData('readCourse.interaction.interactable_id'));
    }

    /**
     * @testdox returns the existing notifications
     */
    public function testReadNotifications(): void
    {
        $expectedCount = 5;

        $user = $this->actingAsNewUser();

        for ($i = 0; $i < $expectedCount; $i++) {
            $user->notifyNow(new CommentReplyNotification(Comment::random(), Comment::random()));
        }

        $query = <<<'GRAPHQL'
        query {
            me {
                id
                username
                notifications(first: 10) {
                    data {
                        id
                        type
                        data
                        read_at
                    }
                }
            }
        }
        GRAPHQL;

        $this->graphQL($query);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertCount($expectedCount, $this->getData('me.notifications.data'));
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
            ['id' => Course::random()->id]
        );

        $this->assertResponseOk();
        $this->assertEquals(
            trans('auth.missing_scope', ['scope' => 'notes:read']),
            $this->getContent('errors.0.message')
        );
    }
}
