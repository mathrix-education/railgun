<?php

namespace App\Observers;

use App\Models\Chapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
     * After save, purge chapter-related cache.
     *
     * @param Chapter $chapter
     */
    public function saved(Chapter $chapter)
    {
        // Purge cache
        DB::table("cache")
            ->where("key", "LIKE", "chapters.grade.{$chapter->grade_id}.discipline.{$chapter->discipline_id}.%")
            ->delete();
        DB::table("cache")
            ->where("key", "LIKE", "user.%.chapter.{$chapter->id}.recursive")
            ->delete();
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


    /**
     * After deletion, purge chapter-related cache.
     *
     * @param Chapter $chapter
     */
    public function deleted(Chapter $chapter)
    {
        // Purge cache
        DB::table("cache")
            ->where("key", "LIKE", "chapters.grade.{$chapter->grade_id}.discipline.{$chapter->discipline_id}.%")
            ->delete();
    }
}
