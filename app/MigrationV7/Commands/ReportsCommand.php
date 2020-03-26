<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use function collect;
use function implode;

class ReportsCommand extends V7Command
{
    private const REPORT_TABLES = ['courses', 'exercises', 'flashcards', 'exams'];

    protected $signature = 'v7:reports';

    public function handle(): void
    {
        collect(self::REPORT_TABLES)
            ->tap(fn() => $this->title('Migrating reports'))
            ->map(static function (string $table) {
                return DB::table($table)
                    ->selectRaw(implode(',', [
                        "CAST(REPLACE(mapping, 'module-', '') as UNSIGNED) as module_id",
                        "'" . Str::singular($table) . "' AS reportable_type",
                        'id AS reportable_id',
                    ]))
                    ->get();
            })
            ->collapse()
            ->mapWithKeys(static function (stdClass $mapping) {
                $moduleId = $mapping->module_id;
                unset($mapping->module_id);

                return [$moduleId => $mapping];
            })
            ->tap(fn() => $this->migrateTo('2020_01_17_042808_polymorphic_reports'))
            ->tap(fn(Collection $mappings) => $this->getOutput()->progressStart($mappings->count()))
            ->each(function (stdClass $mapping, int $moduleId) {
                DB::table('reports')
                    ->where('module_id', '=', $moduleId)
                    ->update((array)$mapping);

                $this->getOutput()->progressAdvance();
            })
            ->tap(fn(Collection $mappings) => $this->getOutput()->progressFinish());
    }
}
