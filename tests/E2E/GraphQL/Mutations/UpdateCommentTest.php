<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Comment;
use Tests\E2E\GraphQL\GraphQLTestCase;

class UpdateCommentTest extends GraphQLTestCase
{
    public function testCommentUpdateUser(): void
    {
        $comment = Comment::random();
        $this->actingAs($comment->user);

        $newContent = $this->faker->text(100);

        $this->executeCommentUpdate([
            'id'      => $comment->id,
            'content' => $newContent,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($comment->id, $this->getData('id'));
        $this->assertEquals($newContent, $this->getData('content'));
    }

    /**
     * @param mixed[] $commentInput The UpdateComment GraphQL Input.
     */
    protected function executeCommentUpdate(array $commentInput): void
    {
        $this->graphQL(
            <<<'GQL'
            mutation ($input: UpdateComment!) {
                updateComment(input: $input) {
                    id
                    content
                }
            }
            GQL,
            ['input' => $commentInput],
        );
    }

    public function testCommentUpdateSomeoneElse(): void
    {
        $comment = Comment::random();
        $this->actingAs();

        $newContent = $this->faker->text(100);

        $this->executeCommentUpdate([
            'id'      => $comment->id,
            'content' => $newContent,
        ]);

        $this->assertResponseOk();
        $this->assertErrorCode('MISSING_PERMISSION');
        $this->assertErrorCategory('authorization');
    }
}
