<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Railgun\Buster\Pipelines\ExamPipeline;
use Railgun\Buster\Utils\SetOrder;
use stdClass;
use function implode;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

/**
 * Migrate modules => exams
 */
class ExamsCommand extends V7Command
{
    private const CHUNK_SIZE = 100;
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:exams';
    /** @var ExamPipeline $pipeline The exam pipeline */
    protected ExamPipeline $pipeline;

    public function handle(ExamPipeline $pipeline): void
    {
        $this->pipeline = $pipeline;

        $this->title('Migrating modules => exams');
        $courses = $this->parseCourseModules();
        $this->line("Got {$courses->count()} course exams modules");

        $tests = $this->parseTestModules();
        $this->line("Got {$courses->count()} test exams modules");

        $courses->merge($tests)
            ->pipe(new SetOrder('chapter_id'))
            ->tap(fn($exams) => $this->getOutput()->progressStart($exams->count()))
            ->chunk(self::CHUNK_SIZE)
            ->each(function (Collection $exams): void {
                DB::table('exams')->insert($exams->toArray());
                $this->getOutput()->progressAdvance($exams->count());
            })
            ->tap(fn() => $this->getOutput()->progressFinish());
    }

    /**
     * Retrieve exams from course modules.
     *
     * @return Collection|stdClass[]
     */
    private function parseCourseModules(): Collection
    {
        return DB::table('modules')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', static function (JoinClause $join): void {
                $join->on('activities.notion_id', '=', 'notions.id')
                    ->where('notions.name', 'LIKE', '%erroga%');
            })
            ->where('modules.type', '=', 'course')
            ->selectRaw(implode(',', [
                'activities.name AS name',
                'modules.content AS content',
                'activities.premium AS premium',
                'modules.visible AS visible',
                'notions.chapter_id AS chapter_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->get()
            ->map(static function (stdClass $exam) {
                $exam->slug     = Str::slug($exam->name);
                $exam->duration = null;
                $content        = json_decode($exam->content, false, 512, JSON_THROW_ON_ERROR);
                $exam->content  = json_encode($content->silicate, JSON_THROW_ON_ERROR, 512);

                return (array)$exam;
            });
    }

    /**
     * Retrieve exams from test modules.
     *
     * @return Collection|stdClass[]
     */
    private function parseTestModules(): Collection
    {
        return DB::table('modules')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->where('activities.type', '=', 'test')
            ->selectRaw(implode(',', [
                'modules.*',
                'activities.name AS name',
                'activities.premium AS premium',
                'notions.chapter_id AS chapter_id',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->orderBy('notions.chapter_id')
            ->get()
            ->map(fn(stdClass $exam) => $this->pipeline->send($exam))
            ->reject(static fn($exam) => $exam === false)
            ->map(static function (array $exam) {
                unset($exam['id'], $exam['activity_id']);

                return $exam;
            });
    }
}
