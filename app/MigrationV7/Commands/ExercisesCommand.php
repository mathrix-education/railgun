<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\MigrationV7\Silicate\ExerciseConverter;
use stdClass;
use const JSON_THROW_ON_ERROR;
use function implode;
use function json_encode;

class ExercisesCommand extends V7Command
{
    private const CHUNK_SIZE = 500;

    protected $signature = 'v7:exercises';

    public function handle(): void
    {
        $this->title('Migrating modules => exercises');

        $iterator  = 0;
        $subjectId = null;

        DB::table('modules')
            ->where('type', '=', 'mcq')
            ->join('subjects', static function (JoinClause $join) {
                $join->on(
                    'subjects.mapping',
                    '=',
                    DB::raw("CONCAT('activity-', modules.activity_id)")
                );
            })
            ->selectRaw(implode(',', [
                'modules.type AS type',
                'modules.questions AS questions',
                'modules.choices AS choices',
                'modules.solutions AS solutions',
                'modules.corrections AS explanations',
                'modules.visible AS visible',
                'subjects.id AS subject_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->orderBy('subjects.id')
            ->distinct()
            ->get()
            ->tap(fn(Collection $data) => $this->line("Found {$data->count()} exercises"))
            ->map(static function (stdClass $original) use (&$iterator, &$subjectId) {
                // Convert fronts and backs to Silicate format
                $exercise = ExerciseConverter::new()->convert($original);

                $exercise->questions    = json_encode($exercise->questions, JSON_THROW_ON_ERROR, 512);
                $exercise->choices      = json_encode($exercise->choices, JSON_THROW_ON_ERROR, 512);
                $exercise->solutions    = json_encode($exercise->solutions, JSON_THROW_ON_ERROR, 512);
                $exercise->explanations = json_encode($exercise->explanations, JSON_THROW_ON_ERROR, 512);

                // Set order
                if ($subjectId !== $exercise->subject_id) {
                    // Subject id has changed
                    $iterator  = 0;
                    $subjectId = $exercise->subject_id;
                }

                $exercise->order = $iterator;
                $iterator++;

                return (array)$exercise;
            })
            ->chunk(self::CHUNK_SIZE)
            ->each(fn(Collection $data) => DB::table('exercises')->insert($data->toArray()));
    }
}
