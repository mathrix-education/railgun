<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use App\Models\Discipline;

class DisciplineObserver extends BaseObserver
{
    /**
     * @throws ForeignConstraints
     */
    public function deleting(Discipline $discipline) : void
    {
        $this->handleRelationDeletion($discipline, 'chapters');

        $discipline->grades()->detach();
    }
}
