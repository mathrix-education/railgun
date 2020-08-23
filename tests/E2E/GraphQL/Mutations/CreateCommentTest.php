<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Jobs\DeliverCommentReplyNotifications;
use Railgun\Models\Comment;
use Railgun\Models\Course;
use Tests\E2E\GraphQL\GraphQLTestCase;

class CreateCommentTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation($input: CreateComment!) {
        createComment(input: $input) {
            id
            content
            score
        }
    }
    GRAPHQL;

    public function testCreateRootComment(): void
    {
        Queue::fake();

        $user    = $this->withPermissions();
        $course  = Course::random();
        $content = $this->faker->text;

        $this->graphQL(self::QUERY, [
            'input' => [
                'content'          => $content,
                'commentable_type' => 'course',
                'commentable_id'   => $course->id,
            ],
        ]);
        $this->assertResponseOk();
        $this->assertNoErrors();

        $this->seeInDatabase('comments', [
            'content'          => $content,
            'parent_id'        => null,
            'user_id'          => $user->id,
            'commentable_type' => 'course',
            'commentable_id'   => $course->id,
        ]);

        Queue::assertNotPushed(DeliverCommentReplyNotifications::class);
    }

    public function testCreateChildComment(): void
    {
        Queue::fake();

        $user            = $this->withPermissions();
        $parent          = Comment::random();
        $content         = $this->faker->text;
        $commentableType = $parent->commentable_type;

        $this->graphQL(self::QUERY, [
            'input' => [
                'content'          => $content,
                'parent_id'        => $parent->id,
                'commentable_type' => $commentableType,
                'commentable_id'   => $parent->commentable_id,
            ],
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
