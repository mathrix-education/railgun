<?php

namespace App\Observers;

use App\Models\Chapter;
use App\Models\Path;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class ChapterObserver.
 *
 * @author    Mathrix Back-End Team
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Observers
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
        // Sluggify if needed
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
        if (!$chapter->notions->isEmpty()) {
            throw new Http400BadRequestException(
                ["chapter" => $chapter],
                "Cannot delete chapter {$chapter->id} since it has referenced notions."
            );
        }

        return true;
    }
}
