<?php

namespace App\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Models\Activity;
use App\Models\Path;

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
     * @param Activity $activity
     *
     * @return bool
     */
    public function saving($activity)
    {
        // Sluggify if needed
        if (empty($activity->slug) ||
            ($activity->isDirty("name") && !$activity->isDirty("slug"))) {
            $activity->slug = str_slug($activity->name);
        }

        return true;
    }


    /**
     * Detach all modules and notions linked with the deleting notion.
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function deleting($activity)
    {
        return true;
    }
}
