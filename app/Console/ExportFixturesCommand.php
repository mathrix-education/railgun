<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;
use stdClass;
use function app;
use function collect;
use function file_put_contents;
use function json_encode;
use function method_exists;
use function str_replace;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class ExportFixturesCommand extends Command
{
    protected const TABLES = [
        'countries',
        'disciplines',
        'grades',
        'grade_training',
        'themes',
        'chapters',
        'packs',
        'trainings',
        'pack_training',
    ];

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'db:export:fixtures';

    /**
     * @return int The exit code.
     *
     * @throws JsonException
     */
    public function handle(): int
    {
        $yes = $this->confirm('You are going to export the current top-level content models, continue?', true);

        if (!$yes) {
            return self::FAILURE;
        }

        foreach (self::TABLES as $table) {
            $path = str_replace(app()->basePath() . '/', '', $this->export($table));
            $this->line("Exported <info>$table</info> to <info>$path</info>");
        }

        return self::SUCCESS;
    }

    /**
     * Export a table.
     *
     * @param string $table The table name to export.
     *
     * @return string The absolute path of the exported JSOn file.
     *
     * @throws JsonException
     */
    protected function export(string $table): string
    {
        $exportMethod = Str::camel('export_' . $table);

        if (method_exists($this, $exportMethod)) {
            $data = $this->{$exportMethod}();
        } else {
            $data = DB::table($table)->get();
        }

        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);
        $path = app()->databasePath("raws/$table.json");

        file_put_contents($path, $json);

        return $path;
    }

    /**
     * @return Collection|stdClass The grades in an insert-compatible order.
     */
    public function exportGrades(): Collection
    {
        $order = ['5eme', '4eme', '3eme', '2nde', '1ere', 'terminale', 'postbac'];

        return collect($order)->map(static function (string $slug): stdClass {
            return DB::table('grades')->where('slug', '=', $slug)->first();
        });
    }
}
