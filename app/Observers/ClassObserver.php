<?php

namespace App\Observers;

use App\Models\Class_;
use App\Models\Discipline;

/**
 * Class UserObserver.
 *
 * @author Mathrix Team
 */
class ClassObserver extends BaseObserver
{
    /**
     * @param Class_ $class
     *
     * @return bool
     */
    public function saving($class)
    {
        // Sluggify if needed
        if (
            empty($class->slug) ||
            ($class->isDirty("name") && !$class->isDirty("slug"))
        ) {
            $class->slug = str_slug($class->name);
        }

        // Sync disciplines
        if (isset($class->link["disciplines"])) {
            $toSync = [];
            foreach ($class->link["disciplines"] as $order => $disciplineId) {
                if (Discipline::where("id", "=", $disciplineId)
                    ->exists()) {
                    $toSync[$disciplineId] = ["order" => $order];
                }
            }
            $class->disciplines()
                ->sync($toSync);
        }

        if (isset($class->link) || !empty($class->link)) {
            unset($class->link);
        }

        return true;
    }
}
