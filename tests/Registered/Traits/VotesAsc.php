<?php
namespace Tests\Registered\Traits;

use App\Models\Comment;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Passport\Passport;

/**
 * Trait VotesAsc
 * Test votes as registered user
 * Because there are currently only comments which are votable, we will only test them alongside votes
 *
 * @package Tests\Registered\Traits
 */
trait VotesAsc
{
    /*
    |--------------------------------------------------------------------------
    | /votes/{votable_type}/{votable_id}/up
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testVoteUpNotExisting()
    {
        /** @var Comment $comment */
        $comment = Comment::inRandomOrder()->first();

        /** @var User $user */
        $user = User::inRandomOrder()
            ->where('type', '=', $this->user->type)
            ->whereDoesntHave('votes', function (Builder $query) use ($comment) {
                $query->where([
                    'votable_id' => $comment->id,
                    'votable_type' => 'comment'
                ]);
            })
            ->first();

        $scoreBefore = $comment->score;

        Passport::actingAs($user, [$user->type]);
        $this->call('post', '/votes/comment/' . $comment->id . '/up');
        $comment = Comment::find($comment->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('votes', [
            'user_id' => $user->id,
            'votable_id' => $comment->id,
            'votable_type' => 'comment',
            'value' => 1
        ]);
        $this->assertEquals($scoreBefore + 1, $comment->score);
    }


    /**
     * Expect HTTP 200 success
     */
    public function testVoteUpExistingDown()
    {
        /** @var User $user */
        $user = User::inRandomOrder()
            ->where('type', '=', $this->user->type)
            ->with(['votes' => function (HasMany $query) {
                $query->where('value', '=', -1);
            }])
            ->whereHas('votes', function (Builder $query) {
                $query->where('value', '=', -1);
            })
            ->first();

        /** @var Comment $comment */
        $comment = Comment::find($user->votes
            ->where('votable_type', '=', 'comment')
            ->pluck('votable_id')
            ->random()
        );

        $scoreBefore = $comment->score;

        Passport::actingAs($user, [$user->type]);
        $this->call('post', '/votes/comment/' . $comment->id . '/up');
        $comment = Comment::find($comment->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('votes', [
            'user_id' => $user->id,
            'votable_id' => $comment->id,
            'votable_type' => 'comment',
            'value' => 1
        ]);
        $this->assertEquals($scoreBefore + 2, $comment->score);
    }


    /**
     * Expect HTTP 200 success
     */
    public function testVoteUpExistingUp()
    {
        /** @var User $user */
        $user = User::inRandomOrder()
            ->where('type', '=', $this->user->type)
            ->with(['votes' => function (HasMany $query) {
                $query->where('value', '=', 1);
            }])
            ->whereHas('votes', function (Builder $query) {
                $query->where('value', '=', 1);
            })
            ->first();

        /** @var Comment $comment */
        $comment = Comment::find($user->votes
            ->where('votable_type', '=', 'comment')
            ->pluck('votable_id')
            ->random()
        );

        $scoreBefore = $comment->score;

        Passport::actingAs($user, [$user->type]);
        $this->call('post', '/votes/comment/' . $comment->id . '/up');
        $comment = Comment::find($comment->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('votes', [
            'user_id' => $user->id,
            'votable_id' => $comment->id,
            'votable_type' => 'comment'
        ]);
        $this->assertEquals($scoreBefore - 1, $comment->score);
    }


    /*
    |--------------------------------------------------------------------------
    | /votes/{votable_type}/{votable_id}/down
    |--------------------------------------------------------------------------
    */
    /**
     * Expect HTTP 200 success
     */
    public function testVoteDownNotExisting()
    {
        /** @var Comment $comment */
        $comment = Comment::inRandomOrder()->first();

        /** @var User $user */
        $user = User::inRandomOrder()
            ->where('type', '=', $this->user->type)
            ->whereDoesntHave('votes', function (Builder $query) use ($comment) {
                $query->where([
                    'votable_id' => $comment->id,
                    'votable_type' => 'comment'
                ]);
            })
            ->first();

        $scoreBefore = $comment->score;

        Passport::actingAs($user, [$user->type]);
        $this->call('post', '/votes/comment/' . $comment->id . '/down');
        $comment = Comment::find($comment->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('votes', [
            'user_id' => $user->id,
            'votable_id' => $comment->id,
            'votable_type' => 'comment',
            'value' => -1
        ]);
        $this->assertEquals($scoreBefore - 1, $comment->score);
    }


    /**
     * Expect HTTP 200 success
     */
    public function testVoteDownExistingUp()
    {
        /** @var User $user */
        $user = User::inRandomOrder()
            ->where('type', '=', $this->user->type)
            ->with(['votes' => function (HasMany $query) {
                $query->where('value', '=', 1);
            }])
            ->whereHas('votes', function (Builder $query) {
                $query->where('value', '=', 1);
            })
            ->first();

        /** @var Comment $comment */
        $comment = Comment::find($user->votes
            ->where('votable_type', '=', 'comment')
            ->pluck('votable_id')
            ->random()
        );

        $scoreBefore = $comment->score;

        Passport::actingAs($user, [$user->type]);
        $this->call('post', '/votes/comment/' . $comment->id . '/down');
        $comment = Comment::find($comment->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->seeInDatabase('votes', [
            'user_id' => $user->id,
            'votable_id' => $comment->id,
            'votable_type' => 'comment',
            'value' => -1
        ]);
        $this->assertEquals($scoreBefore - 2, $comment->score);
    }


    /**
     * Expect HTTP 200 success
     */
    public function testVoteDownExistingDown()
    {
        /** @var User $user */
        $user = User::inRandomOrder()
            ->where('type', '=', $this->user->type)
            ->with(['votes' => function (HasMany $query) {
                $query->where('value', '=', -1);
            }])
            ->whereHas('votes', function (Builder $query) {
                $query->where('value', '=', -1);
            })
            ->first();

        /** @var Comment $comment */
        $comment = Comment::find($user->votes
            ->where('votable_type', '=', 'comment')
            ->pluck('votable_id')
            ->random()
        );

        $scoreBefore = $comment->score;

        Passport::actingAs($user, [$user->type]);
        $this->call('post', '/votes/comment/' . $comment->id . '/down');
        $comment = Comment::find($comment->id);

        // Assertions
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('votes', [
            'user_id' => $user->id,
            'votable_id' => $comment->id,
            'votable_type' => 'comment'
        ]);
        $this->assertEquals($scoreBefore + 1, $comment->score);
    }
}