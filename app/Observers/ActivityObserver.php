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
     * @param Activity $activity
     *
     * @return bool
     */
    public function saving(Activity $activity)
    {
        // Set the slug if needed
        if (empty($activity->slug) ||
            ($activity->isDirty("name") && !$activity->isDirty("slug"))) {
            $activity->slug = Str::slug($activity->name);
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
