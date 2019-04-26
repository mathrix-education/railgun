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
