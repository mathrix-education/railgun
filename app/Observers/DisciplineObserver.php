<?php

namespace App\Observers;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\Class_;
use App\Models\Discipline;

/**
 * Class DisciplineObserver.
 *
 * @author Mathrix Team
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
        // Sluggify if needed
        if (empty($discipline->slug) ||
            ($discipline->isDirty("name") && !$discipline->isDirty("slug"))) {
            $discipline->slug = str_slug($discipline->name);
        }

        // Sync classes
        if (!empty($discipline->link["classes"])) {
            $toSync = [];

            foreach ($discipline->link["classes"] as $classData) {
                [$id, $visible] = [$classData["id"], $classData["visible"]];

                if (Class_::where("id", "=", $id)->exists()) {
                    $toSync[$id] = [
                        "visible" => $visible
                    ];
                }
            }

            $discipline->classes()->sync($toSync);
        }

        if (isset($discipline->link) || !empty($discipline->link)) {
            unset($discipline->link);
        }

        return true;
    }

    /**
     * @param Discipline $discipline
     *
     * @throws Http400InvalidRequestException
     */
    public function deleting($discipline)
    {
        if ($discipline->has("chapters")) {
            throw new Http400InvalidRequestException("Discipline id `{$discipline->id}` still has chapters.");
        } elseif ($discipline->has("classes")) {
            throw new Http400InvalidRequestException("Discipline id `{$discipline->id}` still has classes.");
        }
    }
}
