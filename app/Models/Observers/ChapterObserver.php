<?php

namespace App\Models\Observers;

use App\Models\Chapter;

/**
 * Class ChapterObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
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
        // Ensure the name is present
        if (!$chapter->validate(['name' => 'required|max:255'])) {
            return false;
        }

        // Sluggify if needed
        if (
            empty($chapter->slug) ||
            ($chapter->isDirty('name') && !$chapter->isDirty('slug'))
        ) {
            $chapter->slug = str_slug($chapter->name);
        }

        return parent::saving($chapter);
    }

    /**
     * Delete all notions linked with the deleting chapter
     *
     * @param Chapter $chapter
     *
     * @return bool
     */
    public function deleting($chapter)
    {
        foreach ($chapter->notions as $notion) {
            $notion->delete();
        }

        return true;
    }
}