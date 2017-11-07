<?php
namespace App\Models\Observers;

use App\Models\Activity;
use App\Models\Module;

/**
 * Class ModuleObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class ModuleObserver extends BaseObserver
{
    /**
     * @param Module $module
     *
     * @return bool
     */
    public function saving($module)
    {
        // Sync activities
        if (!empty($module->link["activities"])) {
            $toSync = [];
            foreach($module->link["activities"] as $order => $notionId) {
                if (Activity::find($notionId)->exists()) {
                    $toSync[$notionId] = ["order" => $order];
                }
            }
            if (!empty($toSync)) {
                $module->activities()->sync($toSync);
            }
            unset($module->link["activities"]);
        }

        return true;
    }


    /**
     * Detach all activities linked with the deleting module
     *
     * @param Module $module
     *
     * @return bool
     */
    public function deleting($module)
    {
        $module->activities()->detach();

        return true;
    }
}