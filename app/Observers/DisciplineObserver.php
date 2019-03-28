<?php

namespace App\Observers;

use App\Models\Discipline;
use App\Models\Grade;

/**
 * Class DisciplineObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class DisciplineObserver extends BaseObserver
{
    /**
     * @param Discipline $discipline
     *
     * @return bool
     */
    public function saving($discipline)
    {
        // Set the slug if needed
        if (empty($discipline->slug) ||
            ($discipline->isDirty("name") && !$discipline->isDirty("slug"))) {
            $discipline->slug = str_slug($discipline->name);
        }

        // Sync classes
        if (!empty($discipline->link["classes"])) {
            $toSync = [];

            foreach ($discipline->link["classes"] as $order => $classData) {
                [$id, $visible] = [$classData["id"], $classData["visible"]];

                if (Grade::query()->where("id", "=", $id)->exists()) {
                    $toSync[$id] = [
                        "order" => $order,
                        "visible" => $visible
                    ];
                }
            }

            $discipline->grades()->sync($toSync);
        }

        if (isset($discipline->link) || !empty($discipline->link)) {
            unset($discipline->link);
        }

        return true;
    }


    /**
     * @param Discipline $discipline
     *
     * @throws \App\Exceptions\ForeignConstraintsException
     */
    public function deleting(Discipline $discipline)
    {
        $this->handleRelationDeletion($discipline, "grades");

        $discipline->grades()->detach();
    }
}
