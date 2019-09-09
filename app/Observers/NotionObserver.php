<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Notion;

class NotionObserver extends BaseObserver
{
    /**
     * After save.
     * Update parent chapters's notion counter.
     */
    public function saved(Notion $notion) : bool
    {
        if ($notion->wasChanged('chapter_id', 'visible')) {
            $notion->chapter->updateCounters();
            $notion->unsetRelation('chapter');
        }

        return true;
    }

    /**
     * @throws ForeignConstraintsException
     */
    public function deleting(Notion $notion) : bool
    {
        $this->handleRelationDeletion($notion, 'activities');

        return true;
    }
}
