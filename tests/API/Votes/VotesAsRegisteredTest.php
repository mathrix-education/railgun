<?php
namespace Tests\API\Votes;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Passport\Passport;

/**
 * Class VotesAsPublicTest
 * Test Votes as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\Votes
 */
class VotesAsRegisteredTest extends VotesAsPublicTest
{
    /**
     * POST /votes/{votable_type}/{votable_id}/up
     */
    public function testUp()
    {
        $votableType = $this->faker->randomElement(Vote::VOTABLE_TYPES);
        /** @var Comment|Post $votableClass */
        $votableClass = Relation::getMorphedModel($votableType);
        $userVoteIds = $this->user->votes->pluck("votable_id")->toArray();
        /** @var Comment|Post $votable */
        $votable = $votableClass::whereNotIn("id", $userVoteIds)->firstOrFail();

        $scoreBefore = $votable->score;

        $this->call("post", "/votes/$votableType/{$votable->id}/up");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore + 1, $votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/down
     */
    public function testDown()
    {
        $votableType = $this->faker->randomElement(Vote::VOTABLE_TYPES);
        /** @var Comment|Post $votableClass */
        $votableClass = Relation::getMorphedModel($votableType);
        $userVoteIds = $this->user->votes->pluck("votable_id")->toArray();
        /** @var Comment|Post $votable */
        $votable = $votableClass::whereNotIn("id", $userVoteIds)->firstOrFail();
        $scoreBefore = $votable->score;

        $this->call("post", "/votes/$votableType/{$votable->id}/down");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore - 1, $votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/up
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

        $this->call("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/up");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore - 1, $vote->votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/down
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

        $this->call("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/down");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore + 1, $vote->votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/up
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

        $this->call("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/up");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore + 2, $vote->votable->score);
    }


    /**
     * POST /votes/{votable_type}/{votable_id}/down
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

        $this->call("post", "/votes/{$vote->votable_type}/{$vote->votable_id}/down");

        $this->assertHttp200Success();
        $this->assertEquals($scoreBefore - 2, $vote->votable->score);
    }


    /**
     * POST /tests/wtf/4242666/up
     * Test with a not votable model
     */
    public function testNotVotable() {
        $this->call("post", "/votes/wtf/4242666/up");

        $this->assertHttp400InvalidRequest();
    }
}
