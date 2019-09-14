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
    public function saved(Chapter $chapter) : void
    {
        // Purge cache
        DB::table('cache')
            ->where('key', 'LIKE', "grade.$chapter->grade_id.discipline.$chapter->discipline_id.chapters.%")
            ->delete();
        DB::table('cache')
            ->where('key', 'REGEXP', "^user\.[0-9]+\.chapter\.{$chapter->id}\.recursive$")
            ->delete();
    }

    /**
     * @param Chapter $chapter
     *
     * @return bool
     * @throws ForeignConstraints
     */
    public function deleting(Chapter $chapter) : bool
    {
        $this->handleRelationDeletion($chapter, 'notions');

        return true;
    }

    /**
     * After deletion, purge chapter-related cache.
     *
     * @param Chapter $chapter
     */
    public function deleted(Chapter $chapter) : void
    {
        // Purge cache
        DB::table('cache')
            ->where('key', 'LIKE', "chapters.grade.{$chapter->grade_id}.discipline.{$chapter->discipline_id}.%")
            ->delete();
    }
}
