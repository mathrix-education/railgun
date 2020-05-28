<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Comment;
use Railgun\Models\Course;
use Railgun\Models\Interaction;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function trans;

/**
 * @testdox Query readCourse
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
        $user        = $this->actingAsGetUser();
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
        $this->assertErrorCode('MISSING_SCOPE');
        $this->assertErrorCategory('authorization');
        $this->assertErrorMessage(trans('auth.missing_scope', ['scope' => 'notes:read']));
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
                    premium
                }
            }
            GRAPHQL,
            ['id' => $course->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($course->id, $this->getData('id'));
        $this->assertEmpty($this->getData('content'));
    }

    /**
     * @testdox prevent users data leaks
     */
    public function testNoUserDataLeak(): void
    {
        /** @var Comment $comment */
        $comment = Comment::query()
            ->inRandomOrder()
            ->where('commentable_type', '=', 'course')
            ->whereNull('parent_id')
            ->firstOrFail();
        $course  = $comment->commentable;

        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readCourse(id: $id) {
                    comments {
                        user {
                            email
                        }
                    }
                }
            }
            GRAPHQL,
            ['id' => $course->id],
        );

        $this->assertResponseOk();
        $this->assertErrorCode('POLICY_VIOLATION');
    }

    /**
     * @testdox prevent users data leaks
     */
    public function testAllowUserDataForAuthorizedUsers(): void
    {
        /** @var Comment $comment */
        $comment = Comment::query()
            ->inRandomOrder()
            ->where('commentable_type', '=', 'course')
            ->whereNull('parent_id')
            ->firstOrFail();
        $course  = $comment->commentable;

        $this->withPermissions('users:read');

        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readCourse(id: $id) {
                    comments {
                        user {
                            email
                        }
                    }
                }
            }
            GRAPHQL,
            ['id' => $course->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
    }
}
