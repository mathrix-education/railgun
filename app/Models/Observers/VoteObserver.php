<?php
namespace App\Models\Observers;

use App\Models\Vote;

/**
 * Class VoteObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class VoteObserver extends BaseObserver
{
    /**
     * @param Vote $vote
     */
    public function created($vote)
    {
        $vote->votable->update(["score" => (int)$vote->votable->score + (int)$vote->value]);
    }


    /**
     * @param Vote $vote
     */
    public function updated($vote)
    {
        $vote->votable->update(["score" => (int)$vote->votable->score + 2 * (int)$vote->value]);
    }


    /**
     * @param Vote $vote
     */
    public function deleted($vote)
    {
        $vote->votable->update(["score" => (int)$vote->votable->score - (int)$vote->value]);
    }
}