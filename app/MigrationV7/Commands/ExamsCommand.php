<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use App\MigrationV7\Exams\ExamConverter;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use Symfony\Component\Process\Process;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use function file_put_contents;
use function implode;
use function json_decode;
use function json_encode;
use function ksort;
use function storage_path;

class ExamsCommand extends V7Command
{
    private const CHUNK_SIZE    = 100;
    private const MALFORMED_DIR = 'artifacts/malformed-exams';

    protected $signature = 'v7:exams';

    public function handle(): void
    {
        $this->title('Migrating modules => exams');
        $courses = $this->parseCourseModules();
        $this->line("Got {$courses->count()} course exams modules");

        $tests = $this->parseTestModules();
        $this->line("Got {$courses->count()} test exams modules");

        $exams = $courses->merge($tests)->sortBy('chapter_id');

        // Set order
        $chapterId = 0;
        $iterator  = 0;

        $exams = $exams->map(static function (stdClass $exam) use (&$chapterId, &$iterator) {
            if ($exam->chapter_id !== $chapterId) {
                $chapterId = $exam->chapter_id;
                $iterator  = 0;
            }

            $exam->order   = ++$iterator;
            $exam->content = json_encode($exam->content, JSON_THROW_ON_ERROR, 512);

            $examArray = (array)$exam;
            ksort($examArray);

            return $examArray;
        });

        $this->getOutput()->progressStart($exams->count());
        $exams->chunk(self::CHUNK_SIZE)->each(function (Collection $exams) {
            DB::table('exams')->insert($exams->toArray());
            $this->getOutput()->progressAdvance($exams->count());
        });
        $this->getOutput()->progressFinish();
    }

    private function parseCourseModules(): Collection
    {
        return DB::table('modules')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', static function (JoinClause $join) {
                $join->on('activities.notion_id', '=', 'notions.id')
                    ->where('notions.name', 'LIKE', '%erroga%');
            })
            ->where('modules.type', '=', 'course')
            ->selectRaw(implode(',', [
                'activities.name AS name',
                'modules.content AS content',
                'modules.visible AS visible',
                'notions.chapter_id AS chapter_id',
                'modules.created_at AS created_at',
                'modules.updated_at AS updated_at',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->get()
            ->map(static function (stdClass $exam) {
                $exam->slug = Str::slug($exam->name);

                $exam->duration = null;

                $content       = json_decode($exam->content, false, 512, JSON_THROW_ON_ERROR);
                $exam->content = $content->silicate;

                return $exam;
            });
    }

    private function parseTestModules(): Collection
    {
        $exams = DB::table('modules')
            ->join('activities', 'modules.activity_id', '=', 'activities.id')
            ->join('notions', 'activities.notion_id', '=', 'notions.id')
            ->where('activities.type', '=', 'test')
            ->selectRaw(implode(',', [
                'modules.*',
                'activities.name AS name',
                'notions.chapter_id AS chapter_id',
                "CONCAT('module-', modules.id) AS mapping",
            ]))
            ->orderBy('notions.chapter_id')
            ->get()
            ->map(static function (stdClass $exam) {
                $exam->slug = Str::slug($exam->name);
                unset($exam->id, $exam->activity_id);

                return $exam;
            });

        $malformed = new Collection();

        $this->line("Found {$exams->count()} exams");
        $valid = $exams->map(fn(stdClass $exam) => ExamConverter::decode($exam))
            ->filter(static function (stdClass $exam) use ($malformed) {
                $normalizable = ExamConverter::isNormalizable($exam);

                if (!$normalizable) {
                    $malformed->push($exam);
                }

                return $normalizable;
            })
            ->map(fn(stdClass $exam) => ExamConverter::normalize($exam))
            ->filter(static function (stdClass $exam) use ($malformed) {
                $isMalformed = isset($exam->malformed);

                if ($isMalformed) {
                    $malformed->push($exam);
                }

                return !$isMalformed;
            })
            ->map(fn(stdClass $exam) => ExamConverter::reshape($exam))
            ->map(fn(stdClass $exam) => ExamConverter::strip($exam));

        $this->newLine();
        $this->line('After parsing, got:');
        $this->table(['kind', 'count'], [
            ['<comment>malformed</comment>', $malformed->count()],
            ['<info>valid</info>', $valid->count()],
            ['<comment>total</comment>', $exams->count()],
        ]);
        $this->newLine();

        if ($malformed->isNotEmpty()) {
            $this->exportMalformed($malformed);
        }

        return $valid->map(static function (stdClass $exam) {
            $exam->content = json_encode($exam->content, JSON_THROW_ON_ERROR, 512);

            return $exam;
        });
    }

    /**
     * Export malformed exams to separate JSON files.
     *
     * @param Collection $exams The malformed exams collection.
     */
    private function exportMalformed(Collection $exams): void
    {
        $exportDir = storage_path(self::MALFORMED_DIR);
        Process::fromShellCommandline("rm -rf $exportDir")->run();
        Process::fromShellCommandline("mkdir -p $exportDir")->run();

        $exams->each(static function ($exam) use ($exportDir) {
            $examDest   = "{$exportDir}/{$exam->mapping}.json";
            $examString = json_encode($exam, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);
            file_put_contents($examDest, $examString);
        });

        $this->info("Exported {$exams->count()} malformed exams to $exportDir");
        $this->newLine();
    }
}
