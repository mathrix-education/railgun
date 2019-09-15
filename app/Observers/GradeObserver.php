<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use App\Models\Grade;

class GradeObserver extends BaseObserver
{
    /**
     * @param Grade $grade
     *
     * @throws ForeignConstraints
     */
    public function deleting(Grade $grade): void
    {
        $this->handleRelationDeletion($grade, 'children');
        $this->handleRelationDeletion($grade, 'chapters');
        $this->handleRelationDeletion($grade, 'users');

        $grade->disciplines()->detach();
    }
}
