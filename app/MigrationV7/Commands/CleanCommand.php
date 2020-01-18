<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Facades\DB;

class CleanCommand extends V7Command
{
    protected $signature = 'v7:clean';

    public function handle(): void
    {
        $this->title('Cleaning non-associated comments');

        $this->migrateTo('2020_01_17_042809_foreign_keys_cascade');

        $commentCount = DB::table('comments')
            ->whereNull('commentable_id')
            ->delete();
        $this->line("Deleted $commentCount invalid comments");

        $reportCount = DB::table('reports')
            ->whereNull('reportable_id')
            ->delete();
        $this->line("Deleted $reportCount invalid reports");

        $this->migrateTo('2020_01_18_032807_drop_mapping_columns');
    }
}
