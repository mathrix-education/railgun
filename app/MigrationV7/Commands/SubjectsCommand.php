<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use function count;
use function implode;

class SubjectsCommand extends V7Command
{
    protected $signature = 'v7:subjects';

    public function handle(): void
    {
        $this->title('Migrating activities => subjects');

        $query = DB::table('activities')
            ->join('modules', static function (JoinClause $join) {
                $join->on('activities.id', '=', 'modules.activity_id')
                    ->where('modules.type', '=', 'mcq');
            })
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->selectRaw(implode(',', [
                'activities.name AS name',
                'activities.visible AS visible',
                'activities.notion_id AS notion_id',
                'activities.created_at AS created_at',
                'activities.updated_at AS updated_at',
                "CONCAT('activity-', activities.id) AS mapping",
            ]))
            ->whereNotNull('activities.notion_id')
            ->orderBy('activities.notion_id')
            ->distinct();

        $subjects = $query->get()->toArray();
        $this->line('Found ' . count($subjects) . ' subjects');
        $iterator = 0;
        $notionId = null;

        foreach ($subjects as $key => $subject) {
            if ($notionId !== $subject->notion_id) {
                $iterator = 0;
                $notionId = $subject->notion_id;
            }

            $subject->order = $iterator;
            $subjects[$key] = (array)$subject;
            $iterator++;
        }

        DB::table('subjects')->insert($subjects);
    }
}
