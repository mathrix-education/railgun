<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use Symfony\Component\Process\Process;
use function env;
use function explode;
use function storage_path;
use function str_repeat;
use function str_replace;
use function strlen;

class InteractionsCommand extends V7Command
{
    private const EXPORT_NAME = 'artifacts/interactions.csv';

    protected $signature = 'v7:interactions';

    private Collection $moduleMappings;
    private Collection $activity;

    public function handle(): void
    {
        $this->moduleMappings = new Collection();

        $this->generateMappings('courses');
        $this->generateMappings('exams');
        $this->generateMappings('exercises');
        $this->generateMappings('flashcards');
        $this->export();
    }

    private function export(): void
    {
        $this->line('Exporting marks table');

        $dbFile = storage_path(self::EXPORT_NAME);
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT', 3306);
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        $mysqlArgs  = "$dbName --host=$dbHost --port=$dbPort -u'$dbUser' -p'$dbPass'";
        $mysqlQuery = 'SELECT * FROM marks';
        $sed        = 'sed \'s/\t/","/g;s/^/"/;s/$/"/;s/\n//g\'';

        $cmd = "mysql $mysqlArgs -e '$mysqlQuery' | pv | $sed  > $dbFile";

        $this->line(str_replace($dbPass, str_repeat('*', strlen($dbPass)), $cmd));
        $restoreProcess = Process::fromShellCommandline($cmd);
        $restoreProcess->setTimeout(300);
        $restoreProcess->setTty(Process::isTtySupported());
        $restoreProcess->run();
    }

    private function generateMappings(string $table): void
    {
        $this->line("Generating $table mappings");
        DB::table($table)
            ->get(['id', 'mapping'])
            ->mapWithKeys(static function (stdClass $raw) use ($table) {
                $parts     = explode('-', $raw->mapping);
                $mappingId = (int)$parts[1];

                return [
                    $mappingId => [
                        'interactable_type' => Str::singular($table),
                        'interactable_id'   => $raw->id,
                    ],
                ];
            })
            ->tap(fn($data) => $this->moduleMappings->merge($data));
    }
}
