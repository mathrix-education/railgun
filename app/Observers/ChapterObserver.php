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
     * Before creating the chapter, put it at the end of the related chapters (same grade an discipline).
     *
     * @param Chapter $chapter
     *
     * @return bool
     */
    public function creating(Chapter $chapter)
    {
        $order = DB::table("chapters")
            ->selectRaw("MAX(`order`) as max_order")
            ->where("discipline_id", "=", $chapter->discipline_id)
            ->where("grade_id", "=", $chapter->grade_id)
            ->get()
            ->pluck("max_order")
            ->first();
        $chapter->order = $order !== null ? $order + 1 : 0;

        return true;
    }


    /**
     * @param Chapter $chapter
     *
     * @return bool
     */
    public function saving($chapter)
    {
        // Set the slug if needed
        if (empty($chapter->slug) || ($chapter->isDirty("name") && !$chapter->isDirty("slug"))) {
            $chapter->slug = Str::slug($chapter->name);
        }

        return true;
    }


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
}
