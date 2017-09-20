<?php
namespace Tests\Registered\Traits;

use App\Models\Comment;

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
    public function testVoteUpNotExisting()
    {
        /** @var Comment $comment */
        $comment = Comment::inRandomOrder()->first();
        $voters = [];

        /** @var Vote $vote */
        foreach($comment->votes as $vote) {
            $voters = $vote->user;
        }
    }


    public function testVoteUpExistingDown()
    {

    }


    public function testVoteUpExistingUp()
    {

    }


    /*
    |--------------------------------------------------------------------------
    | /votes/{votable_type}/{votable_id}/down
    |--------------------------------------------------------------------------
    */
    public function testVoteDownNotExisting()
    {

    }


    public function testVoteDownExistingUp()
    {

    }


    public function testVoteDownExistingDown()
    {

    }
}