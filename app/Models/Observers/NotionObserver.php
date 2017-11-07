<?php
namespace App\Models\Observers;

use App\Models\Activity;
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

        // Sync activities
        if (!empty($notion->link["activities"])) {
            $toSync = [];
            foreach($notion->link["activities"] as $order => $notionId) {
                if (Activity::find($notionId)->exists()) {
                    $toSync[$notionId] = ["order" => $order];
                }
            }
            if (!empty($toSync)) {
                $notion->activities()->sync($toSync);
            }
            unset($notion->link["activities"]);
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
        $notion->activities()->detach();

        return true;
    }
}