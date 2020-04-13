<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use function implode;

/**
 * Migrate activities => subjects
 */
class SubjectsCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:subjects';

    public function handle(): void
    {
        $this->title('Migrating activities => subjects');

        DB::table('activities')
            ->join('modules', static function (JoinClause $join): void {
                $join->on('activities.id', '=', 'modules.activity_id')
                    ->where('modules.type', '=', 'mcq');
            })
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->selectRaw(implode(',', [
                'activities.name AS name',
                "activities.order AS 'order'",
                'activities.premium AS premium',
                'activities.visible AS visible',
                'activities.notion_id AS notion_id',
                'activities.created_at AS created_at',
                'activities.updated_at AS updated_at',
                "CONCAT('activity-', activities.id) AS mapping",
            ]))
            ->whereNotNull('activities.notion_id')
            ->orderBy('activities.notion_id')
            ->distinct()
            ->get()
            ->tap(fn(Collection $subjects) => $this->line("Found {$subjects->count()} subjects"))
            ->map(static function (stdClass $subject): array {
                $subject->slug = Str::slug($subject->name);

                return (array)$subject;
            })
            ->tap(static fn(Collection $subjects) => DB::table('subjects')->insert($subjects->toArray()));
    }
}
