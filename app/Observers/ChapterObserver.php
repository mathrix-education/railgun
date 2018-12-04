<?php

namespace App\Observers;

use App\Exceptions\Http422ModelSaveException;
use App\Models\Chapter;
use App\Models\Notion;
use App\Models\Path;

/**
 * Class ChapterObserver.
 *
 * @author Mathrix Team
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
        if (empty($chapter->slug) ||
            ($chapter->isDirty("name") && !$chapter->isDirty("slug"))
        ) {
            $chapter->slug = str_slug($chapter->name);
        }
        $chapter->slug = str_slug($chapter->name);

        // Sync notions
        if (isset($chapter->link["notions"])) {
            $toSync = [];
            foreach ($chapter->link["notions"] as $order => $notionId) {
                if (Notion::whereId($notionId)
                    ->exists()) {
                    $toSync[$notionId] = ["order" => $order];
                }
            }
            $chapter->notions()
                ->sync($toSync);
        }

        if (isset($chapter->link)) {
            unset($chapter->link);
        }

        return true;
    }

    /**
     * Delete all notions linked with the deleting chapter.
     *
     * @param Chapter $chapter
     *
     * @return bool
     */
    public function deleting($chapter)
    {
        $chapter->notions()
            ->detach();
        Path::where("chapter_id", "=", $chapter->id)
            ->get()
            ->each(function (Path $path) {
                $path->delete();
            });

        return true;
    }
}
