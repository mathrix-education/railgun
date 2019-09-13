<?php

declare(strict_types=1);

namespace Tests\API;

use App\Models\Comment;
use App\Models\Vote;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;

/**
 * Class VotesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class VotesTest extends APIStatelessTestCase
{
    /**
     * POST /votes/comment/{commentId}
     */
    public function testVote()
    {
        $comment = Comment::random();
        $value   = $this->faker->boolean ? 1 : -1;
        $uri     = '/votes/comment/{$comment->id}';
        $user    = JWT::autoScope('post', $uri);

        $this->json('post', '/votes/comment/{$comment->id}', ['value' => $value]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);

        $this->assertInDatabase('votes', [
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'value' => $value,
        ]);
    }

    /**
     * POST /votes/comment/{commentId} with vote value override
     */
    public function testVoteOverride()
    {
        $vote = Vote::random();
        JWT::actingAs($vote->user);
        $value = $vote->value * -1;

        $this->json('post', '/votes/comment/{$vote->comment->id}', ['value' => $value]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);

        $this->assertInDatabase('votes', [
            'id' => $vote->id,
            'user_id' => $vote->user->id,
            'comment_id' => $vote->comment->id,
            'value' => $value,
        ]);
    }

    /**
     * DELETE /votes/comment/{commentId}
     */
    public function testUnVote()
    {
        $vote = Vote::random();
        JWT::actingAs($vote->user);
        $this->json('delete', '/votes/comment/{$vote->comment->id}');

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);

        $this->assertNotInDatabase('votes', ['id' => $vote->id]);
    }
}
