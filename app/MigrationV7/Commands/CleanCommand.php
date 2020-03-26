<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Facades\DB;
use function array_unique;

class CleanCommand extends V7Command
{
    protected $signature = 'v7:clean';

    public function handle(): void
    {
        $this->title('Cleaning non-associated comments');

        $this->migrateTo('2020_01_17_042809_foreign_keys_cascade');

        $commentCount = DB::table('comments')
            ->whereNull('commentable_id')
            ->orWhereNotNull('deleted_at')
            ->delete();
        $this->line("Deleted $commentCount invalid comments");

        $reportCount = DB::table('reports')
            ->whereNull('reportable_id')
            ->delete();
        $this->line("Deleted $reportCount invalid reports");

        $this->migrateTo('2020_01_18_032807_drop_mapping_columns');

        /** @var int[] $courseIds */
        $courseIds = DB::table('courses')
            ->get(['notion_id'])
            ->pluck('notion_id')
            ->toArray();
        /** @var int[] $subjectIds */
        $subjectIds = DB::table('subjects')
            ->get(['notion_id'])
            ->pluck('notion_id')
            ->toArray();

        $whitelist   = array_unique([...$courseIds, ...$subjectIds]);
        $notionCount = DB::table('notions')
            ->whereNotIn('id', $whitelist)
            ->delete();
        $this->line("Deleted $notionCount invalid notions");
    }
}
