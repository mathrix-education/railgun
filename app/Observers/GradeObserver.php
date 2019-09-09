<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Grade;

class GradeObserver extends BaseObserver
{
    /**
     * @throws ForeignConstraintsException
     */
    public function deleting(Grade $grade) : void
    {
        $this->handleRelationDeletion($grade, 'children');
        $this->handleRelationDeletion($grade, 'chapters');
        $this->handleRelationDeletion($grade, 'users');

        $grade->disciplines()->detach();
    }
}
