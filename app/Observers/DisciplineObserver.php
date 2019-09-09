<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Discipline;

class DisciplineObserver extends BaseObserver
{
    /**
     * @throws ForeignConstraintsException
     */
    public function deleting(Discipline $discipline) : void
    {
        $this->handleRelationDeletion($discipline, 'chapters');

        $discipline->grades()->detach();
    }
}
