<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CleanCommand extends V7Command
{
    protected $signature = 'v7:clean';

    public function handle(): void
    {
        $this->title('Cleaning non-associated comments');

        DB::table('comments')
            ->whereNull('commentable_id')
            ->get(['id'])
            ->pluck('id')
            ->tap(function (Collection $commentIds) {
                $this->line("Found {$commentIds->count()} invalid comments");

                $votesCount = DB::table('votes')
                    ->whereIn('comment_id', $commentIds->toBase())
                    ->delete();
                $this->line("Deleted $votesCount invalid votes");

                $commentChildCount  = DB::table('comments')
                    ->whereIn('parent_id', $commentIds->toArray())
                    ->delete();
                $commentParentCount = DB::table('comments')
                    ->whereIn('id', $commentIds->toArray())
                    ->delete();
                $commentCount       = $commentChildCount + $commentParentCount;
                $this->line("Deleted $commentCount invalid comments");
            });

        $reportCount = DB::table('reports')
            ->whereNull('reportable_id')
            ->delete();
        $this->line("Deleted $reportCount invalid reports");

        $this->migrateTo('2020_01_18_032807_drop_mapping_columns');
    }
}
