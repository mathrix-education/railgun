<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Comment;
use Railgun\Models\Course;
use Railgun\Models\Interaction;
use Tests\E2E\GraphQL\GraphQLTestCase;

class ReadCourseTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
     * @ticket #165
     */
    public function testInteractableIsNotNull(): void
    {
        Queue::fake();

        // Create an interaction
        $user = $this->actingAsGetUser();

        /** @var Course $course */
        $course = Course::query()
            ->where('premium', '=', false)
            ->inRandomOrder()
            ->firstOrFail();

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
                    content
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
        $this->assertEquals('course', $this->getData('readCourse.interaction.interactable_type'));
        $this->assertEquals($course->id, $this->getData('readCourse.interaction.interactable_id'));
    }

    public function testReadNotesMissingPermission(): void
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
        $this->assertErrorCode('MISSING_PERMISSION');
        $this->assertErrorCategory('authorization');
    }

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
