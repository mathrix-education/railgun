<?php

namespace Tests\API_old\Votes;

use App\Models\Comment;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Passport;

/**
 * Class VotesAsRegisteredTest.
 * Test Votes as registered user.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\API\Votes
 */
class VotesAsRegisteredTest extends VotesAsPublicTest
{
    /**
     * POST /votes/{votable_type}/{votable_id}/up.
     */
    public function testUp()
    {
        $userVoteIds = $this->user->votes->pluck("votable_id")->toArray();
        /** @var Comment $comment */
        $comment = Comment::whereNotIn("id", $userVoteIds)->firstOrFail();
        $scoreBefore = $comment->score;

        $this->json("post", "/votes/comment/{$comment->id}/up");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore + 1, $comment->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/down.
     */
    public function testDown()
    {
        $userVoteIds = $this->user->votes->pluck("votable_id")->toArray();
        /** @var Comment $comment */
        $comment = Comment::whereNotIn("id", $userVoteIds)->firstOrFail();
        $scoreBefore = $comment->score;

        $this->json("post", "/votes/comment/{$comment->id}/down");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore - 1, $comment->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/up.
     */
    public function testUpCancel()
    {
        /** @var Vote $vote */
        $vote = Vote::whereHas("user", function (Builder $query) {
            $query->where("type", "=", $this->user->type);
        })
            ->where("value", "=", 1)
            ->firstOrFail();
        /** @var User $user */
        $user = $vote->user;
        $scoreBefore = $vote->votable->score;
        Passport::actingAs($user, [$user->type]);

        $this->json("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/up");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore - 1, $vote->votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/down.
     */
    public function testDownCancel()
    {
        /** @var Vote $vote */
        $vote = Vote::whereHas("user", function (Builder $query) {
            $query->where("type", "=", $this->user->type);
        })
            ->where("value", "=", -1)
            ->firstOrFail();
        /** @var User $user */
        $user = $vote->user;
        $scoreBefore = $vote->votable->score;
        Passport::actingAs($user, [$user->type]);

        $this->json("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/down");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore + 1, $vote->votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/up.
     */
    public function testUpReverse()
    {
        /** @var Vote $vote */
        $vote = Vote::whereHas("user", function (Builder $query) {
            $query->where("type", "=", $this->user->type);
        })
            ->where("value", "=", -1)
            ->firstOrFail();
        /** @var User $user */
        $user = $vote->user;
        $scoreBefore = $vote->votable->score;
        Passport::actingAs($user, [$user->type]);

        $this->json("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/up");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore + 2, $vote->votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/down.
     */
    public function testDownReverse()
    {
        /** @var Vote $vote */
        $vote = Vote::whereHas("user", function (Builder $query) {
            $query->where("type", "=", $this->user->type);
        })
            ->where("value", "=", 1)
            ->firstOrFail();
        /** @var User $user */
        $user = $vote->user;
        $scoreBefore = $vote->votable->score;
        Passport::actingAs($user, [$user->type]);

        $this->json("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/down");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore - 2, $vote->votable->score);
    }


    /**
     * POST /tests/wtf/4242666/up
     * Test with a not votable model.
     */
    public function testNotVotable()
    {
        $this->json("post", "/votes/wtf/4242666/up");

        $this->assertHttp400InvalidRequest();
    }
}
