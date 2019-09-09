<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Chapter;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

class ChapterObserver extends BaseObserver
{
    /**
     * After save, purge chapter-related cache.
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
     * @throws Http400BadRequestException
     */
    public function deleting(Chapter $chapter) : bool
    {
        $this->handleRelationDeletion($chapter, 'notions');

        return true;
    }

    /**
     * After deletion, purge chapter-related cache.
     */
    public function deleted(Chapter $chapter) : void
    {
        // Purge cache
        DB::table('cache')
            ->where('key', 'LIKE', "chapters.grade.{$chapter->grade_id}.discipline.{$chapter->discipline_id}.%")
            ->delete();
    }
}
