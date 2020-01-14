<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function collect;
use function count;
use function implode;

class ExercisesCommand extends V7Command
{
    private const CHUNK_SIZE = 500;

    protected $signature = 'v7:exercises';

    public function handle(): void
    {
        $this->title('Migrating modules => exercises');

        $query = DB::table('modules')
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
            ->distinct();

        $exercises = $query->get()->toArray();
        $this->line('Found ' . count($exercises) . ' exercises');
        $iterator  = 0;
        $subjectId = null;

        foreach ($exercises as $key => $exercise) {
            if ($subjectId !== $exercise->subject_id) {
                $iterator  = 0;
                $subjectId = $exercise->subject_id;
            }

            $exercise->order = $iterator;
            $exercises[$key] = (array)$exercise;
            $iterator++;
        }

        collect($exercises)->chunk(self::CHUNK_SIZE)->each(static function (Collection $exercises) {
            DB::table('exercises')->insert($exercises->toArray());
        });
    }
}
