<?php

declare(strict_types=1);

namespace Railgun\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Railgun\Models\Chapter;
use Railgun\Models\Session;

class ChapterWentInvisible implements ShouldQueue
{
    public function handle(Chapter $chapter): void
    {
        if (!$chapter->visible) {
            return;
        }
        Session::query()
            ->where('chapter_id', '=', "$chapter->id")
            ->delete();
    }
}
