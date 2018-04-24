<?php
namespace App\Models\Observers;

use App\Models\Activity;
use App\Models\Lesson;
use App\Models\Meta;
use App\Models\Module;
use App\Models\Post;
use App\Models\Report;

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
        if (isset($module->link["activities"])) {
            $toSync = [];
            foreach ($module->link["activities"] as $order => $notionId) {
                if (Activity::whereId($notionId)->exists()) {
                    $toSync[$notionId] = ["order" => $order];
                }
            }

            $module->activities()->sync($toSync);
        }

        if (isset($module->link)) {
            unset($module->link);
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
        Meta::whereModuleId($module->id)->delete();
        Post::whereModuleId($module->id)->delete();
        Lesson::whereModuleId($module->id)->delete();
        Report::whereModuleId($module->id)->delete();

        return true;
    }
}
