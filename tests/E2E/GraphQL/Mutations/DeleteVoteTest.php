<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Comment;
use Railgun\Models\Vote;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class DeleteVoteTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    public function testDeleteVote(): void
    {
        /** @var Comment $comment */
        $comment = factory(Comment::class)->create();
        /** @var Vote $vote */
        $vote = factory(Vote::class)->create([
            'value'      => 1,
            'comment_id' => $comment->id,
        ]);

        $comment->refresh();
        $this->assertEquals(1, $comment->score);

        $this->actingAs($vote->user);

        $this->graphQL(
            <<<'GRAPQL'
            mutation($comment_id: ID!) {
                deleteVote(comment_id: $comment_id) {
                    comment {
                        id
                        score
                    }
                    user {
                        id
                    }
                }
            }
            GRAPQL,
            ['comment_id' => $vote->comment_id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        $this->assertEquals(0, $this->getData('comment.score'));
        $this->assertEquals($vote->comment_id, $this->getData('comment.id'));
        $this->assertEquals($vote->user_id, $this->getData('user.id'));

        $this->notSeeInDatabase('votes', [
            'comment_id' => $vote->comment_id,
            'user_id'    => $vote->comment_id,
        ]);
    }
}
