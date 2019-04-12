<?php

namespace App\Observers;

use App\Models\Notion;
use Illuminate\Support\Str;

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
     * @param Notion $notion
     *
     * @return bool
     */
    public function saving($notion)
    {
        // Set the slug if needed
        if (
            empty($notion->slug) ||
            ($notion->isDirty('name') && !$notion->isDirty('slug'))
        ) {
            $notion->slug = Str::slug($notion->name);
        }

        return true;
    }


    /**
     * @param Notion $notion
     *
     * @return bool
     * @throws \App\Exceptions\ForeignConstraintsException
     */
    public function deleting($notion)
    {
        $this->handleRelationDeletion($notion, "activities");

        return true;
    }
}
