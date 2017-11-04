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

        // Attach activities
        if (!empty($notion->link['activities'])) {
            foreach ($notion->link['activities'] as $activityId) {
                if (Activity::find($activityId)->exists()) {
                    $notion->activities()->attach($activityId);

                }
            }
            unset($notion->link['activities']);
        }

        // Detach activities
        if (!empty($notion->unlink['activities'])) {
            foreach ($notion->unlink['activities'] as $activityId) {
                if (Activity::find($activityId)->exists()) {
                    $notion->activities()->detach($activityId);

                }
            }
            unset($notion->unlink['activities']);
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