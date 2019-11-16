<?php

declare(strict_types=1);

namespace App\Observers;

use App\Exceptions\ForeignConstraints;
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;

class ChapterObserver extends BaseObserver
{
    /**
     * After save, purge chapter-related cache.
     *
     * @param Chapter $chapter
     */
    public function saved(Chapter $chapter): void
    {
        /**
         * Since there are performance issues with cache deletion, we will simply get the keys to delete then
         * order the deletion of those keys.
         *
         * @link https://github.com/mathrix-education/education-api/issues/45
         */
        $table = DB::table('cache');

        /** @var string[] $keys The cache keys to delete. */
        $keys = $table
            ->select(['key'])
            ->where('key', 'LIKE', "grade.$chapter->grade_id.discipline.$chapter->discipline_id.chapters.%")
            ->orWhere('key', 'LIKE', "user.%.chapter.{$chapter->id}.recursive")
            ->get()
            ->pluck('key')
            ->toArray();

        $table->whereIn('key', $keys)->delete();
    }

    /**
     * @param Chapter $chapter
     *
     * @return bool
     *
     * @throws ForeignConstraints
     */
    public function deleting(Chapter $chapter): bool
    {
        $this->handleRelationDeletion($chapter, 'notions');

        return true;
    }

    /**
     * After deletion, purge chapter-related cache.
     *
     * @param Chapter $chapter
     */
    public function deleted(Chapter $chapter): void
    {
        // Purge cache
        DB::table('cache')
            ->where('key', 'LIKE', "chapters.grade.{$chapter->grade_id}.discipline.{$chapter->discipline_id}.%")
            ->delete();
    }
}
