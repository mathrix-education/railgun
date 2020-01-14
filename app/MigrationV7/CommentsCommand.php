<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function implode;
use function strtolower;

/**
 * 1. Migrate the module_id of the comments table to their new polymorphic column.
 * 2. Clean not-associated comments.
 */
class CommentsCommand extends V7Command
{
    protected $signature = 'v7:comments';

    public function handle(): void
    {
        $this->title('Migrating comments polymorphic foreign keys');
        $mappings = new Collection();
        $tables   = ['courses', 'exercises', 'flashcards'];

        foreach ($tables as $table) {
            $this->line("Generating $table mappings");
            $singular = strtolower(Str::singular($table));

            $tableMappings = DB::table($table)
                ->selectRaw(implode(',', [
                    'id AS new_id',
                    "CAST(REPLACE(mapping, 'module-', '') as UNSIGNED) as module_id",
                ]))
                ->get();
            $this->line("Found {$tableMappings->count()} $table mappings");

            foreach ($tableMappings as $mapping) {
                $mappings->push([
                    $singular,
                    $mapping->new_id,
                    $mapping->module_id,
                ]);
            }
        }

        $this->line("Patching {$mappings->count()} comments");
        $progress = $this->getOutput()->createProgressBar($mappings->count());
        $mappings->each(static function ($mapping) use ($progress) {
            [$commentableType, $commentableId, $commentId] = $mapping;
            DB::table('comments')
                ->where('id', '=', $commentId)
                ->update([
                    'commentable_type' => $commentableType,
                    'commentable_id'   => $commentableId,
                ]);
            $progress->advance();
        });
        $progress->finish();
        $this->getOutput()->newLine();
    }
}
