<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Support\Facades\DB;

/**
 * Drop the 'nouveau-chapitre' and other invalid chapters.
 */
class ChaptersCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:chapters';

    public function handle(): void
    {
        $this->title('Dropping invalid chapters');

        $chapterIds = DB::table('chapters')
            ->whereIn('slug', ['nouveau-chapitre', ''])
            ->get(['id'])
            ->pluck('id');

        $notionsQuery = DB::table('notions')->whereIn('chapter_id', $chapterIds);
        $notionIds    = $notionsQuery->get(['id'])->pluck('id');
        DB::table('courses')->whereIn('notion_id', $notionIds)->delete();
        $notionsQuery->delete();
        DB::table('flashcards')->whereIn('chapter_id', $chapterIds)->delete();
        DB::table('chapters')->whereIn('id', $chapterIds)->delete();
    }
}
