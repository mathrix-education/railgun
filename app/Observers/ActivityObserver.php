<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Activity;
use Illuminate\Support\Str;

/**
 * Class ActivityObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class ActivityObserver extends BaseObserver
{
    /**
     * After save.
     * Update parent notion's activity counter.
     * Update parent chapter's activity counter.
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function saved(Activity $activity)
    {
        if ($activity->wasChanged(["notion_id", "visible"])) {
            $activity->notion->updateCounters();
            $activity->notion->chapter->updateCounters();
        }

        return true;
    }


    /**
     * @param Activity $activity
     *
     * @return bool
     * @throws ForeignConstraintsException
     */
    public function deleting(Activity $activity)
    {
        $this->handleRelationDeletion($activity, "modules");

        return true;
    }
}
