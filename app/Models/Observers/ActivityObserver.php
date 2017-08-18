<?php
namespace App\Models\Observers;

use App\Models\Activity;
use Mathrix\Lumen\Observers\BaseObserver;

/**
 * Class ActivityObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
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
        // Ensure the name is present
        if (!$activity->validate(['name' => 'required|max:255'])) {
            return false;
        }

        // Sluggify if needed
        if (
            empty($activity->slug) ||
            ($activity->isDirty('name') && !$activity->isDirty('slug'))
        ) {
            $activity->slug = str_slug($activity->name);
        }

        return parent::saving($activity);
    }
}