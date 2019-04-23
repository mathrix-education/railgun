<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Discipline;
use Illuminate\Support\Str;

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
            $discipline->slug = Str::slug($discipline->name);
        }

        return true;
    }


    /**
     * @param Discipline $discipline
     *
     * @throws ForeignConstraintsException
     */
    public function deleting(Discipline $discipline)
    {
        $this->handleRelationDeletion($discipline, "chapters");

        $discipline->grades()->detach();
    }
}
