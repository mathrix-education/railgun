<?php

namespace App\Models\Observers;

use App\Models\Activity;

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


        // Attach notions
        if (!empty($activity->link['notions'])) {
            foreach ($activity->link['notions'] as $notionId) {
                if (Activity::find($notionId)->exists()) {
                    $activity->notions()->attach($notionId);
                }
            }
            unset($activity->link['notions']);
        }

        // Detach notions
        if (!empty($activity->unlink['notions'])) {
            foreach ($activity->unlink['notions'] as $notionId) {
                if (Activity::find($notionId)->exists()) {
                    $activity->notions()->attach($notionId);
                }
            }
            unset($activity->unlink['notions']);
        }

        return parent::saving($activity);
    }


    /**
     * Detach all modules and notions linked with the deleting notion
     *
     * @param Activity $activity
     *
     * @return bool
     */
    public function deleting($activity)
    {
        $activity->notions()->detach();
        $activity->modules()->detach();

        return true;
    }
}