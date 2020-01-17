<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;
use function fopen;
use function fwrite;
use function implode;
use function storage_path;

class InteractionsCommand extends V7Command
{
    protected $signature = 'v7:interactions';

    private string $migrationFilePath;
    private        $migrationFileHandle;

    public function handle(): void
    {
        $this->setup();
        $this->writeHeader();
        $this->writeData('course', 'module');
        $this->writeData('exercise', 'module');
        $this->writeData('exam', 'module');
        $this->writeData('flashcard', 'module');
        $this->writeData('subject', 'activity');
        $this->writeFooter();
    }

    private function setup(): void
    {
        $this->migrationFilePath   = storage_path('artifacts/mathrix_education_v7_interactions.sql');
        $this->migrationFileHandle = fopen($this->migrationFilePath, 'wb');
    }

    private function writeHeader(): void
    {
        $header = <<<MYSQL
        INSERT INTO `interactions`
        VALUES 
        MYSQL;

        fwrite($this->migrationFileHandle, $header);
    }

    private function writeFooter(): void
    {
        $footer = <<<MYSQL
        ON DUPLICATE KEY UPDATE
        comprehension = VALUES(comprehension),
        score = VALUES(score),
        created_at = VALUES(created_at),
        updated_at = VALUES(updated_at)
        MYSQL;

        fwrite($this->migrationFileHandle, $footer);
    }

    private function writeData(string $target, string $source): void
    {
        $targets = Str::plural($target);

        $this->line("Migrating marks => interactions ($targets)");

        $page  = 0;
        $total = 0;
        $break = false;

        while (!$break) {
            DB::reconnect();

            $values = $this->query($target, $source, ++$page, 500)
                ->get()
                ->tap(function (Collection $interactions) use (&$total, &$break) {
                    $count = $interactions->count();

                    if ($count === 0) {
                        $break = true;
                    }

                    $this->getOutput()->write('.');
                    if (($total + 1) % 60000 === 0) {
                        $this->getOutput()->write("\n");
                    }

                    $total += $count;
                })
                ->map(static function (stdClass $interaction) {
                    return implode(', ', [
                        $interaction->user_id,
                        "'{$interaction->interactable_type}'",
                        $interaction->interactable_id,
                        $interaction->comprehension !== null ? "'{$interaction->comprehension}'" : 'NULL',
                        $interaction->score ?? 'NULL',
                        $interaction->created_at !== null ? "'{$interaction->created_at}'" : 'NULL',
                        $interaction->updated_at !== null ? "'{$interaction->updated_at}'" : 'NULL',
                    ]);
                })
                ->map(fn(string $values) => "($values)")
                ->implode(",\n");

            if ($break) {
                break;
            }

            fwrite($this->migrationFileHandle, "{$values},\n");
            $query = <<<MYSQL
            INSERT INTO `interactions`
            VALUES $values
            ON DUPLICATE KEY UPDATE
            comprehension = VALUES(comprehension),
            score = VALUES(score),
            created_at = VALUES(created_at),
            updated_at = VALUES(updated_at)
            MYSQL;

            DB::unprepared($query);
        }

        $this->line('');
        $this->line("Processed {$total} marks");
    }

    private function query(string $target, string $source, int $page, int $chunkSize = 1000): Builder
    {
        $targetTable = Str::plural($target);
        $sourceTable = Str::plural($source);

        return DB::table($targetTable)
            ->join($sourceTable, "$targetTable.mapping", '=', DB::raw("CONCAT('$source-', $sourceTable.id)"))
            ->join('marks', static function (JoinClause $join) use ($source, $sourceTable) {
                $join->on("$sourceTable.id", '=', 'marks.markable_id')
                    ->where('marks.markable_type', '=', $source);
            })
            ->selectRaw(implode(', ', [
                'marks.user_id AS user_id',
                "'$target' AS interactable_type",
                "$targetTable.id AS interactable_id",
                <<<MYSQL
                CASE
                   WHEN marks.status = 'completed' THEN 'clear'
                   WHEN marks.status = 'to_revise' THEN 'blur'
                END AS comprehension
                MYSQL,
                'NULL AS score',
                'marks.created_at AS created_at',
                'marks.updated_at AS updated_at',
            ]))
            ->limit($chunkSize)
            ->offset($page * $chunkSize);
    }
}
