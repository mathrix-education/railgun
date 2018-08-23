<?php

namespace App\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Meta;
use App\Models\Notion;

/**
 * Class NotionObserver.
 *
 * @author Mathrix Team
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
            foreach ($notion->link["chapters"] as $order => $chapterId) {
                if (Chapter::whereId($chapterId)
                    ->exists()) {
                    $toSync[$chapterId] = ["order" => $order];
                }
            }

            $notion->chapters()
                ->sync($toSync);
        }

        // Sync activities
        if (isset($notion->link["activities"])) {
            $toSync = [];
            foreach ($notion->link["activities"] as $order => $activityId) {
                if (Activity::whereId($activityId)
                    ->exists()) {
                    $toSync[$activityId] = ["order" => $order];
                }
            }

            $notion->activities()
                ->sync($toSync);
        }

        if (isset($notion->link)) {
            unset($notion->link);
        }

        return true;
    }

    /**
     * Detach all activities link with the deleting notion.
     *
     * @param Notion $notion
     *
     * @throws Http422ModelSaveException
     *
     * @return bool
     */
    public function deleting($notion)
    {
        $notion->chapters()
            ->detach();
        $notion->activities()
            ->detach();
        Meta::whereNotionId($notion->id)
            ->delete();

        return true;
    }
}
