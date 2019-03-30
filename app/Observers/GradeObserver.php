<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Discipline;
use App\Models\Grade;
use Illuminate\Support\Str;

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
        // Set the slug if needed
        if (
            empty($grade->slug) ||
            ($grade->isDirty("name") && !$grade->isDirty("slug"))
        ) {
            $grade->slug = Str::slug($grade->name);
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


    /**
     * @param Grade $grade
     *
     * @throws ForeignConstraintsException
     */
    public function deleting(Grade $grade)
    {
        $this->handleRelationDeletion($grade, "children");
        $this->handleRelationDeletion($grade, "chapters");
        $this->handleRelationDeletion($grade, "users");

        $grade->disciplines()->detach();
    }
}
