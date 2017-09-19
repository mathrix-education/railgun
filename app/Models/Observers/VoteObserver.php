<?php

namespace App\Models\Observers;

use App\Models\Activity;
use App\Models\Notion;
use App\Models\Vote;
use Mathrix\Lumen\Observers\BaseObserver;

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
     *
     * @return bool
     */
    public function saving($vote)
    {


        return parent::saving($vote);
    }
}