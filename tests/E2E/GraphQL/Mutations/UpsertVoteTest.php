<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Comment;
use Railgun\Models\Vote;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

/**
 * Mutation upsertVote.
 */
class UpsertVoteTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
     * @ticket #262
     */
    public function testInsertVote(): void
    {
        $user = $this->withPermissions();
        /** @var Comment $comment */
        $comment = factory(Comment::class)->create();

        $this->executeUpsertVote($comment->id, 'up');

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($comment->id, $this->getData('upsertVote.comment.id'));
        $this->assertEquals($user->id, $this->getData('upsertVote.user.id'));

        $this->assertEquals(1, $comment->refresh()->score);
        $this->seeInDatabase('votes', [
            'comment_id' => $comment->id,
            'user_id'    => $user->id,
            'value'      => 1,
        ]);
    }

    private function executeUpsertVote(int $commentId, string $value): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateVote!) {
                upsertVote(input: $input) {
                    id
                    value
                    comment {
                        id
                    }
                    user {
                        id
                    }
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'comment_id' => $commentId,
                    'value'      => $value,
                ],
            ],
        );
    }

    /**
     * @ticket #262
     */
    public function testUpdateVote(): void
    {
        $user = $this->withPermissions();
        /** @var Comment $comment */
        $comment = factory(Comment::class)->create();
        $vote    = new Vote(['user_id' => $user->id, 'comment_id' => $comment->id, 'value' => 1]);
        $vote->save();

        $this->executeUpsertVote($comment->id, 'down');

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($comment->id, $this->getData('upsertVote.comment.id'));
        $this->assertEquals($user->id, $this->getData('upsertVote.user.id'));

        $this->assertEquals(-1, $comment->refresh()->score);
        $this->seeInDatabase('votes', [
            'id'         => $vote->id,
            'comment_id' => $comment->id,
            'user_id'    => $user->id,
            'value'      => -1,
        ]);
    }
}
