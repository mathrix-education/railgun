<?php
namespace App\Models\Observers;

use App\Models\Activity;
use App\Models\Module;
use Mathrix\Lumen\Observers\BaseObserver;

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
        // Ensure the name is present
        if (!$module->validate(['name' => 'required|max:255'])) {
            $module->setResponse($module->http422Validation([
                'message' => $module->errors()
            ]));

            return false;
        }

        // Sluggify if needed
        if (
            empty($module->slug) ||
            ($module->isDirty('name') && !$module->isDirty('slug'))
        ) {
            $module->slug = str_slug($module->name);
        }

        // Attach activities
        if (!empty($module->link['activities'])) {
            foreach ($module->link['activities'] as $activityId) {
                if(Activity::find($activityId)->exists()) {
                    $module->activities()->attach($activityId);

                }
            }
            unset($module->link['activities']);
        }

        // Detach activities
        if (!empty($module->unlink['activities'])) {
            foreach ($module->unlink['activities'] as $activityId) {
                if(Activity::find($activityId)->exists()) {
                    $module->activities()->attach($activityId);

                }
            }
            unset($module->unlink['activities']);
        }

        return parent::saving($module);
    }
}