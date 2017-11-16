<?php
namespace App\Models\Observers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Notion;

/**
 * Class NotionObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class NotionObserver extends BaseObserver
{
    /**
     * @param Notion $notion
     *
     * @return bool
     */
    public function saving($notion)
    {
        // Sluggify if needed
        if (
            empty($notion->slug) ||
            ($notion->isDirty('name') && !$notion->isDirty('slug'))
        ) {
            $notion->slug = str_slug($notion->name);
        }

        // Sync chapters
        if (isset($notion->link["chapters"])) {
            $toSync = [];
            foreach($notion->link["chapters"] as $order => $chapterId) {
                if (Chapter::find($chapterId)->exists()) {
                    $toSync[$chapterId] = ["order" => $order];
                }
            }

            $notion->chapters()->sync($toSync);
        }

        // Sync activities
        if (isset($notion->link["activities"])) {
            $toSync = [];
            foreach($notion->link["activities"] as $order => $activityId) {
                if (Activity::find($activityId)->exists()) {
                    $toSync[$activityId] = ["order" => $order];
                }
            }

            $notion->activities()->sync($toSync);
        }

        if (isset($notion->link)) {
            unset($notion->link);
        }

        return true;
    }


    /**
     * Detach all activities link with the deleting notion
     *
     * @param Notion $notion
     *
     * @return bool
     */
    public function deleting($notion)
    {
        $notion->chapters()->detach();
        $notion->activities()->detach();

        return true;
    }
}