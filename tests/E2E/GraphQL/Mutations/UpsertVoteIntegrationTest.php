<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Comment;
use Railgun\Models\Vote;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * Mutation upsertVote.
 */
class UpsertVoteIntegrationTest extends GraphQLIntegrationTestCase
{
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
     * @testdox inserts a new up vote.
     */
    public function testInsertVote(): void
    {
        $user    = $this->actingAsNewUser();
        $comment = Comment::random();

        $this->executeUpsertVote($comment->id, 'UP');

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($comment->id, $this->getData('upsertVote.comment.id'));
        $this->assertEquals($user->id, $this->getData('upsertVote.user.id'));
        $this->seeInDatabase('votes', [
            'comment_id' => $comment->id,
            'user_id'    => $user->id,
            'value'      => 1,
        ]);
    }

    /**
     * @testdox replaces an existing up vote by a down vote.
     */
    public function testUpdateVote(): void
    {
        $user    = $this->actingAsNewUser();
        $comment = Comment::random();
        $vote    = new Vote(['user_id' => $user->id, 'comment_id' => $comment->id, 'value' => 1]);
        $vote->save();

        $this->executeUpsertVote($comment->id, 'DOWN');

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($comment->id, $this->getData('upsertVote.comment.id'));
        $this->assertEquals($user->id, $this->getData('upsertVote.user.id'));
        $this->seeInDatabase('votes', [
            'id'         => $vote->id,
            'comment_id' => $comment->id,
            'user_id'    => $user->id,
            'value'      => -1,
        ]);
    }
}
