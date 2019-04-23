<?php

namespace App\Observers;

use App\Exceptions\ForeignConstraintsException;
use App\Models\Notion;

/**
 * Class NotionObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class NotionObserver extends BaseObserver
{
    /**
     * After save.
     * Update parent chapters's notion counter.
     *
     * @param Notion $notion
     *
     * @return bool
     */
    public function saved(Notion $notion)
    {
        if ($notion->wasChanged("chapter_id", "visible")) {
            $notion->chapter->updateCounters();
            $notion->unsetRelation("chapter");
        }

        return true;
    }


    /**
     * @param Notion $notion
     *
     * @return bool
     * @throws ForeignConstraintsException
     */
    public function deleting($notion)
    {
        $this->handleRelationDeletion($notion, "activities");

        return true;
    }
}
