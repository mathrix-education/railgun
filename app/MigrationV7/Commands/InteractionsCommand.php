<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use Symfony\Component\Mime\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use function array_combine;
use function array_merge;
use function env;
use function explode;
use function fclose;
use function fgetcsv;
use function fopen;
use function in_array;
use function sprintf;
use function storage_path;

class InteractionsCommand extends V7Command
{
    private const EXPORT_NAME = 'artifacts/interactions.csv';
    private const CHUNK_SIZE  = 1000;

    protected $signature = 'v7:interactions';
    
    private string $exportPath;

    private array      $mappings = [
        'activities' => [],
        'modules'    => [],
    ];
    private Collection $interactions;

    public function handle(): void
    {
        $this->setup();
        $this->generateMappings('courses', 'modules');
        $this->generateMappings('exams', 'modules');
        $this->generateMappings('exercises', 'modules');
        $this->generateMappings('flashcards', 'modules');
        $this->generateMappings('subjects', 'activities');
        $this->export();
        $this->build();
        $this->unique();
        $this->insert();
    }

    private function setup(): void
    {
        $this->title('Migrating marks => interactions');
        $this->interactions = new Collection();
    }

    private function export(): void
    {
        $this->line('Exporting marks table');

        $this->exportPath = storage_path(self::EXPORT_NAME);

        // Database configuration
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT', 3306);
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        // Build command
        $mysqlArgs  = "$dbName --host=$dbHost --port=$dbPort -u'$dbUser' -p'$dbPass'";
        $mysqlQuery = "SELECT * FROM marks WHERE markable_type IN('module', 'activity')";
        $sed        = 'sed \'s/\t/","/g;s/^/"/;s/$/"/;s/\n//g\'';

        $cmd = "mysql $mysqlArgs -e \"$mysqlQuery\" | pv | $sed  > {$this->exportPath}";

        $restoreProcess = Process::fromShellCommandline($cmd);
        $restoreProcess->setTimeout(300);
        $restoreProcess->setTty(Process::isTtySupported());
        $restoreProcess->run();
    }

    private function generateMappings(string $table, string $class): void
    {
        DB::table($table)
            ->tap(fn() => $this->line("Generating $table mappings"))
            ->get(['id', 'mapping'])
            ->mapWithKeys(static function (stdClass $raw) use ($table) {
                $parts   = explode('-', $raw->mapping);
                $modelId = (int)$parts[1];

                return [
                    $modelId => [
                        'interactable_type' => Str::singular($table),
                        'interactable_id'   => $raw->id,
                    ],
                ];
            })
            ->tap(function (Collection $data) use ($table, $class) {
                $this->mappings[$class] = array_merge($this->mappings[$class], $data->toArray());
                $this->info(sprintf('> Got %s %s', $data->count(), $table));
            });
    }

    private function build(): void
    {
        $this->line('Building interactions');
        $handle = fopen($this->exportPath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Build');
        }

        $count   = DB::table('marks')->count();
        $headers = null;

        $this->getOutput()->progressStart($count);

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $this->getOutput()->progressAdvance();

            if ($headers === null) {
                $headers = $row;
                continue;
            }

            $data = array_combine($headers, $row);

            if (!in_array($data['markable_type'], ['activity', 'module'])) {
                continue;
            }

            $plural = Str::plural($data['markable_type']);
            $oldId  = (int)$data['markable_id'];

            if (!isset($this->mappings[$plural][$oldId])) {
                continue;
            }

            $mapping = $this->mappings[$plural][$oldId];

            if ($data['status'] === 'completed') {
                $comprehension = 'clear';
            } elseif ($data['status'] === 'to_revise') {
                $comprehension = 'blur';
            } else {
                $comprehension = null;
            }

            $this->interactions->push([
                'user_id'           => $data['user_id'],
                'interactable_type' => $mapping['interactable_type'],
                'interactable_id'   => $mapping['interactable_id'],
                'comprehension'     => $comprehension,
                'score'             => null,
                'created_at'        => $data['created_at'],
                'updated_at'        => $data['updated_at'],
            ]);

            $this->interactions = $this->interactions->values();
        }

        $this->getOutput()->progressFinish();

        fclose($handle);
    }

    private function unique(): void
    {
        $this->line('Generating unique combinations');
        $new = new Collection();

        $this->getOutput()->progressStart($this->interactions->count());

        $this->interactions->each(function (array $interaction) use (&$new) {
            $key = "{$interaction['user_id']}-{$interaction['interactable_type']}-{$interaction['interactable_id']}";

            $new[$key] = $interaction;

            $this->getOutput()->progressAdvance();
        });

        $this->getOutput()->progressFinish();

        $this->interactions = $new->values();
    }

    private function insert(): void
    {
        $this->line('Saving interactions');
        $this->getOutput()->progressStart($this->interactions->count());

        $this->interactions->chunk(self::CHUNK_SIZE)
            ->each(function (Collection $interactions) {
                DB::table('interactions')->insert($interactions->toArray());
                $this->getOutput()->progressAdvance($interactions->count());
            });

        $this->getOutput()->progressFinish();
    }
}
