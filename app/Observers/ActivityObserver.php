<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use App\Models\Activity;

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
    public function saved(Activity $activity) : bool
    {
        if ($activity->wasChanged(['notion_id', 'visible'])) {
            $activity->notion->updateCounters();
            $activity->notion->chapter->updateCounters();
            $activity->unsetRelation('notion');
        }

        return true;
    }

    /**
     * @param Activity $activity
     *
     * @return bool
     * @throws ForeignConstraints
     */
    public function deleting(Activity $activity) : bool
    {
        $this->handleRelationDeletion($activity, 'modules');

        return true;
    }
}
