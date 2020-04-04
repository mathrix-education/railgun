<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Buster\Pipelines\ExercisePipeline;
use Railgun\Buster\Utils\SetOrder;
use stdClass;
use function implode;

/**
 * Migrate MCQ modules => exercises
 */
class ExercisesCommand extends V7Command
{
    private const CHUNK_SIZE = 500;

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:exercises';
    /** @var ExercisePipeline $pipeline The exercise pipeline. */
    private ExercisePipeline $pipeline;

    public function handle(ExercisePipeline $pipeline): void
    {
        $this->pipeline = $pipeline;
        $this->title('Migrating modules => exercises');

        DB::table('modules')
            ->where('modules.type', '=', 'mcq')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('subjects', static function (JoinClause $join): void {
                $join->on(
                    'subjects.mapping',
                    '=',
                    DB::raw("CONCAT('activity-', modules.activity_id)"),
                );
            })
            ->selectRaw(implode(',', [
                'modules.type AS type',
                'modules.questions AS questions',
                'modules.choices AS choices',
                'modules.solutions AS solutions',
                'modules.corrections AS explanations',
                'activities.premium AS premium',
                'modules.visible AS visible',
                'subjects.id AS subject_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->distinct()
            ->get()
            ->tap(fn(Collection $data) => $this->line("Found {$data->count()} exercises"))
            ->map(fn(stdClass $exercise) => $this->pipeline->send($exercise))
            ->reject(static fn($exercise) => $exercise === false)
            ->pipe(new SetOrder('subject_id'))
            ->tap(fn(Collection $exercises) => $this->getOutput()->progressStart($exercises->count()))
            ->chunk(self::CHUNK_SIZE)
            ->each(function (Collection $exercises): void {
                DB::table('exercises')->insert($exercises->toArray());
                $this->getOutput()->progressAdvance($exercises->count());
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }
}
