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
     * @throws ForeignConstraintsException
     */
    public function deleting(Discipline $discipline)
    {
        $this->handleRelationDeletion($discipline, "chapters");

        $discipline->grades()->detach();
    }
}
