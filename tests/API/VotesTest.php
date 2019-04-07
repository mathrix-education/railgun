<?php

namespace Tests\API;

use App\Models\Comment;
use App\Models\Vote;
use Laravel\Passport\Passport;
use Tests\APITestCase;

/**
 * Class VotesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class VotesTest extends APITestCase
{
    /**
     * POST /votes/comment/{commentId}
     */
    public function testVote()
    {
        $comment = Comment::random();
        $value = $this->faker->boolean ? 1 : -1;
        $uri = "/votes/comment/{$comment->id}";
        $this->autoMockScope("post", $uri);

        $this->json("post", "/votes/comment/{$comment->id}", [
            "value" => $value
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $this->assertInDatabase("votes", [
            "user_id" => $this->passportUser->id,
            "comment_id" => $comment->id,
            "value" => $value
        ]);
    }


    /**
     * POST /votes/comment/{commentId} with vote value override
     */
    public function testVoteOverride()
    {
        $vote = Vote::random();
        Passport::actingAs($vote->user, explode(",", $vote->user->getPassportScopes()));
        $value = $vote->value * -1;

        $this->json("post", "/votes/comment/{$vote->comment->id}", [
            "value" => $value
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $this->assertInDatabase("votes", [
            "id" => $vote->id,
            "user_id" => $vote->user->id,
            "comment_id" => $vote->comment->id,
            "value" => $value
        ]);
    }


    /**
     * DELETE /votes/comment/{commentId}
     */
    public function testUnVote()
    {
        $vote = Vote::random();
        Passport::actingAs($vote->user, explode(",", $vote->user->getPassportScopes()));
        $this->json("delete", "/votes/comment/{$vote->comment->id}");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        $this->assertNotInDatabase("votes", ["id" => $vote->id]);
    }
}
