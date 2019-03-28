<?php

namespace App\Observers;

use App\Models\Chapter;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class ChapterObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class ChapterObserver extends BaseObserver
{
    /**
     * @param Chapter $chapter
     *
     * @return bool
     */
    public function saving($chapter)
    {
        // Set the slug if needed
        if (empty($chapter->slug) || ($chapter->isDirty("name") && !$chapter->isDirty("slug"))) {
            $chapter->slug = str_slug($chapter->name);
        }

        return true;
    }


    /**
     * @param Chapter $chapter
     *
     * @return bool
     * @throws Http400BadRequestException
     */
    public function deleting($chapter)
    {
        $this->handleRelationDeletion($chapter, "notions");

        return true;
    }
}
