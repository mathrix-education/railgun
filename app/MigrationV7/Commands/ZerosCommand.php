<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Facades\DB;
use function array_merge;
use function array_reverse;
use function count;

/**
 * Purge models with foreign keys = 0
 */
class ZerosCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'v7:zeros';

    public function handle(): void
    {
        $this->title('Purging models with foreign keys = 0');

        $toDelete = [];

        $notionIds = DB::table('notions')
            ->where('chapter_id', '=', 0)
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $this->line('Found ' . count($notionIds) . ' invalid notions');
        $toDelete[] = ['notions', $notionIds];

        $activityIds = DB::table('activities')
            ->where('notion_id', '=', 0)
            ->orWhereIn('notion_id', $notionIds)
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $this->line('Found ' . count($activityIds) . ' invalid activities');
        $toDelete[] = ['activities', $activityIds];

        // Purge modules
        $moduleIds = DB::table('modules')
            ->where('activity_id', '=', 0)
            ->orWhereIn('activity_id', $activityIds)
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $this->line('Found ' . count($moduleIds) . ' invalid modules');
        $toDelete[] = ['modules', $moduleIds];

        $commentParentIds = DB::table('comments')
            ->whereIn('module_id', $moduleIds)
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $this->line('Found ' . count($commentParentIds) . ' invalid parent comments');
        $toDelete[] = ['comments', $commentParentIds];

        $commentChildIds = DB::table('comments')
            ->whereIn('parent_id', $commentParentIds)
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $this->line('Found ' . count($commentParentIds) . ' invalid child comments');
        $toDelete[] = ['comments', $commentChildIds];

        $voteIds = DB::table('votes')
            ->whereIn('comment_id', array_merge($commentParentIds, $commentChildIds))
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $this->line('Found ' . count($commentParentIds) . ' invalid votes');
        $toDelete[] = ['votes', $voteIds];

        $reports = DB::table('reports')
            ->whereIn('module_id', $moduleIds)
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $this->line('Found ' . count($commentParentIds) . ' invalid reports');
        $toDelete[] = ['reports', $reports];

        foreach (array_reverse($toDelete) as $toDelete) {
            [$table, $ids] = $toDelete;
            DB::table($table)->whereIn('id', $ids)->delete();
        }
    }
}
