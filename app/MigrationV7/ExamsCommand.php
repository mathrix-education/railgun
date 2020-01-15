<?php

declare(strict_types=1);

namespace App\MigrationV7;

use App\MigrationV7\Exams\ExamConverter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use Symfony\Component\Process\Process;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use function file_put_contents;
use function implode;
use function json_encode;
use function storage_path;

class ExamsCommand extends V7Command
{
    private const CHUNK_SIZE       = 100;
    private const MALFORMED_DIR    = 'artifacts/malformed-exams';
    private const DEFAULT_DURATION = 30;

    protected $signature = 'v7:exams';

    public function handle(): void
    {
        $this->title('Migrating modules => exams');
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

        $notNormalizable = new Collection();
        $malformed       = new Collection();

        $this->line("Found {$exams->count()} exams");
        $valid = $exams->map(fn(stdClass $exam) => ExamConverter::decode($exam))
            ->filter(static function (stdClass $exam) use ($notNormalizable) {
                $normalizable = ExamConverter::isNormalizable($exam);

                if (!$normalizable) {
                    $notNormalizable->push($exam);
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

        // Set order
        $chapterId = 0;
        $iterator  = 0;

        $valid = $valid->map(static function (stdClass $exam) use (&$chapterId, &$iterator) {
            if ($exam->chapter_id !== $chapterId) {
                $chapterId = $exam->chapter_id;
                $iterator  = 0;
            }

            $exam->order = $iterator;
            $iterator++;

            return $exam;
        })
            ->values();

        $this->newLine();
        $this->line('After parsing, got:');
        $this->table(['kind', 'count'], [
            ['<comment>not normalizable</comment>', $notNormalizable->count()],
            ['<comment>malformed</comment>', $malformed->count()],
            ['<info>valid</info>', $valid->count()],
            ['<comment>total</comment>', $exams->count()],
        ]);
        $this->newLine();

        if ($malformed->isNotEmpty()) {
            $this->exportMalformed($malformed);
        }

        $this->line('Saving exams');
        $progress = $this->getOutput()->createProgressBar($valid->count());
        $valid
            ->map(static function (stdClass $exam) {
                if ($exam->duration === null) {
                    $exam->duration = self::DEFAULT_DURATION;
                }

                $exam->content = json_encode($exam->content, JSON_THROW_ON_ERROR, 512);

                return (array)$exam;
            })
            ->chunk(self::CHUNK_SIZE)
            ->each(static function (Collection $exams) use ($progress) {
                // DB::table('exams')->insert($exams->toArray());
                $progress->advance($exams->count());
            });
        $progress->finish();
        $this->newLine();
    }

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
