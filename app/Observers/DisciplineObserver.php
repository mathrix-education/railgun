<?php

namespace App\Observers;

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
            $discipline->classes()->sync($discipline->link["classes"]);
        }

        if (isset($discipline->link) || !empty($discipline->link)) {
            unset($discipline->link);
        }

        return true;
    }
}
