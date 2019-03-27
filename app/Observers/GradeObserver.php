<?php

namespace App\Observers;

use App\Models\Discipline;
use App\Models\Grade;

/**
 * Class GradeObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class GradeObserver extends BaseObserver
{
    /**
     * @param Grade $grade
     *
     * @return bool
     */
    public function saving($grade)
    {
        // Sluggify if needed
        if (
            empty($grade->slug) ||
            ($grade->isDirty("name") && !$grade->isDirty("slug"))
        ) {
            $grade->slug = str_slug($grade->name);
        }

        // Sync disciplines
        if (isset($grade->link["disciplines"])) {
            $toSync = [];
            foreach ($grade->link["disciplines"] as $order => $disciplineId) {
                if (Discipline::query()->where("id", "=", $disciplineId)->exists()) {
                    $toSync[$disciplineId] = ["order" => $order];
                }
            }
            $grade->disciplines()->sync($toSync);
        }

        if (isset($grade->link) || !empty($grade->link)) {
            unset($grade->link);
        }

        return true;
    }
}
