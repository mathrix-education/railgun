<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use function collect;
use function implode;

/**
 * Migrate reports
 */
class ReportsCommand extends V7Command
{
    private const REPORT_TABLES = ['courses', 'exercises', 'flashcards', 'exams'];

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'v7:reports';

    public function handle(): void
    {
        $this->title('Migrating reports');
        collect(self::REPORT_TABLES)
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
            ->each(function (stdClass $mapping, int $moduleId): void {
                DB::table('reports')
                    ->where('module_id', '=', $moduleId)
                    ->update((array)$mapping);

                $this->getOutput()->progressAdvance();
            })
            ->tap(fn(Collection $mappings) => $this->getOutput()->progressFinish());
    }
}
