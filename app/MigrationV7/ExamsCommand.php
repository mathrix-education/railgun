<?php

declare(strict_types=1);

namespace App\MigrationV7;

use App\MigrationV7\Exams\ExamConverter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use stdClass;
use ZipArchive;
use function json_encode;
use function storage_path;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class ExamsCommand extends V7Command
{
    private const MALFORMED_ZIP = 'artifacts/malformed-exams.zip';

    protected $signature = 'v7:exams';

    public function handle(): void
    {
        $this->title('Migrating modules => exams');
        $exams = DB::table('modules')
            ->where('type', '=', 'test')
            ->get();

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

        $this->newLine();
        $this->line('After parsing, got:');
        $this->table(['kind', 'count'], [
            ['<comment>not normalizable</comment>', $notNormalizable->count()],
            ['<comment>malformed</comment>', $malformed->count()],
            ['<info>valid</info>', $exams->count()],
            ['<comment>total</comment>', $valid->count()],
        ]);
        $this->newLine();

        if ($malformed->isNotEmpty()) {
            $this->exportMalformed($malformed);
        }

        $this->info('TODO: injection in the database');
    }

    private function exportMalformed(Collection $exams): void
    {
        $zip     = new ZipArchive();
        $zipPath = storage_path(self::MALFORMED_ZIP);

        if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create $zipPath");
        }

        $exams->each(static function ($exam) use ($zip) {
            $examString = json_encode($exam, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);
            $zip->addFromString('exam-' . $exam->id . '.json', $examString);
        });

        $zip->close();

        $this->info("Exported {$exams->count()} malformed exams to $zipPath");
        $this->newLine();
    }
}
