<?php

namespace App\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Models\Activity;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Path;

/**
 * Class ActivityObserver.
 *
 * @author Mathrix Team
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

        // Sync notions
        if (isset($activity->link["notions"])) {
            $toSync = [];
            foreach ($activity->link["notions"] as $order => $notionId) {
                if (Notion::whereId($notionId)
                    ->exists()) {
                    $toSync[$notionId] = ["order" => $order];
                }
            }
            $activity->notions()
                ->sync($toSync);
        }

        // Sync modules
        if (isset($activity->link["modules"])) {
            $toSync = [];
            foreach ($activity->link["modules"] as $order => $moduleId) {
                if (Module::whereId($moduleId)
                    ->exists()) {
                    $toSync[$moduleId] = ["order" => $order];
                }
            }
            $activity->modules()
                ->sync($toSync);
        }

        if (isset($activity->link)) {
            unset($activity->link);
        }

        return true;
    }

    /**
     * Detach all modules and notions linked with the deleting notion.
     *
     * @param Activity $activity
     *
     * @return bool
     * @throws Http422ModelSaveException
     */
    public function deleting($activity)
    {
        $activity->notions()
            ->detach();
        $activity->modules()
            ->detach();
        Path::where("activity_id", "=", $activity->id)
            ->delete();

        return true;
    }
}
