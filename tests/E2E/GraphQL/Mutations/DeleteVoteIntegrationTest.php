<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Vote;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * @testdox [E2] Mutation deleteVote
 */
class DeleteVoteIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox deletes a vote
     */
    public function testDeleteVote(): void
    {
        /** @var Vote $vote */
        $vote = factory(Vote::class)->create();

        $this->actingAs($vote->user);

        $this->graphQL(
            <<<'GRAPQL'
            mutation($comment_id: ID!) {
                deleteVote(comment_id: $comment_id) {
                    comment {
                        id
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
        $this->assertEquals($vote->comment_id, $this->getData('deleteVote.comment.id'));
        $this->assertEquals($vote->user_id, $this->getData('deleteVote.user.id'));
        $this->notSeeInDatabase('votes', [
            'comment_id' => $vote->comment_id,
            'user_id'    => $vote->comment_id,
        ]);
    }
}
