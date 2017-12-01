<?php
namespace App\Models\Observers;

use App\Models\Module;
use App\Models\Section;

/**
 * Class ActivityObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class SectionObserver extends BaseObserver
{
    /**
     * @param Section $section
     *
     * @return bool
     */
    public function saving($section)
    {
        // Sluggify if needed
        if (empty($section->slug) ||
            ($section->isDirty("name") && !$section->isDirty("slug"))) {
            $section->slug = str_slug($section->name);
        }

        // Sync modules
        if (isset($section->link["modules"])) {
            $toSync = [];
            foreach($section->link["modules"] as $order => $moduleId) {
                if (Module::find($moduleId)->exists()) {
                    $toSync[$moduleId] = ["order" => $order];
                }
            }
            $section->modules()->sync($toSync);
        }

        if (isset($section->link)) {
            unset($section->link);
        }

        return true;
    }


    /**
     * Detach all modules and notions linked with the deleting notion
     *
     * @param Section $section
     *
     * @return bool
     */
    public function deleting($section)
    {
        $section->modules()->detach();

        return true;
    }
}