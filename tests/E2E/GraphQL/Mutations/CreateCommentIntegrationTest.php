<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Jobs\DeliverCommentReplyNotifications;
use Railgun\Models\Comment;
use Railgun\Models\Course;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function strtoupper;

/**
 * @testdox [E2E] Mutation createComment
 */
class CreateCommentIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation($content: String!, $parent_id: ID, $commentable_type: CommentableType!, $commentable_id: ID!) {
        createComment(
            content: $content
            parent_id: $parent_id
            commentable_type: $commentable_type
            commentable_id: $commentable_id
        ) {
            id
            content
            score
        }
    }
    GRAPHQL;

    /**
     * @testdox creates a root comment
     */
    public function testCreateRootComment(): void
    {
        Queue::fake();

        $user    = $this->actingAsNewUser();
        $course  = Course::random();
        $content = $this->faker->text;

        $this->graphQL(self::QUERY, [
            'content'          => $content,
            'commentable_type' => 'COURSE',
            'commentable_id'   => $course->id,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $this->seeInDatabase('comments', [
            'content'          => $content,
            'parent_id'        => null,
            'user_id'          => $user->id,
            'commentable_type' => 'COURSE',
            'commentable_id'   => $course->id,
        ]);

        Queue::assertNotPushed(DeliverCommentReplyNotifications::class);
    }

    /**
     * @testdox creates a child comment
     */
    public function testCreateChildComment(): void
    {
        Queue::fake();

        $user            = $this->actingAsNewUser();
        $parent          = Comment::random();
        $content         = $this->faker->text;
        $commentableType = strtoupper($parent->commentable_type);

        $this->graphQL(self::QUERY, [
            'content'          => $content,
            'parent_id'        => $parent->id,
            'commentable_type' => $commentableType,
            'commentable_id'   => $parent->commentable_id,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();

        $this->seeInDatabase('comments', [
            'content'          => $content,
            'parent_id'        => $parent->id,
            'user_id'          => $user->id,
            'commentable_type' => $commentableType,
            'commentable_id'   => $parent->commentable_id,
        ]);

        Queue::assertPushed(DeliverCommentReplyNotifications::class);
    }
}
