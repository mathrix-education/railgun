<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function time;

class InteractionsCommand extends V7Command
{
    protected $signature = 'v7:interactions';

    public function handle(): void
    {
        // $this->migrate('course', 'module');
        $this->migrate('exercise', 'module');
        // $this->migrate('exam', 'module');
        // $this->migrate('flashcard', 'module');
        // $this->migrate('subject', 'activity');
    }

    private function migrate(string $target, string $source, int $page = 0): void
    {
        $countBefore = DB::table('interactions')->count();
        $start       = time();

        $this->line($this->makeScript($target, $source, $page));
        dd('');

        DB::unprepared($this->makeScript($target, $source, $page));

        $duration   = time() - $start;
        $countAfter = DB::table('interactions')->count();
        $delta      = $countAfter - $countBefore;

        if ($countBefore === $countAfter) {
            return;
        }

        $this->line("Inserted {$delta} new interactions in $duration seconds");
        $this->migrate($target, $source, $page + 1);
    }

    private function makeScript(string $target, string $source, int $page, int $chunk = 1000): string
    {
        $targets = Str::plural($target);
        $sources = Str::plural($source);
        $offset  = $page * $chunk;

        return <<<MYSQL
        INSERT INTO interactions (
            SELECT marks.user_id AS user_id,
                '$target'        AS interactable_type,
                $targets.id      AS interactable_id,
                CASE
                    WHEN marks.status = 'completed' THEN 'clear'
                    WHEN marks.status = 'to_revise' THEN 'blur'
                END              AS comprehension,
                NULL             AS score,
                marks.created_at AS created_at,
                marks.updated_at AS updated_at
            FROM $targets
                INNER JOIN $sources on $targets.mapping = CONCAT('$source-', $sources.id)
                INNER JOIN marks on $sources.id = marks.markable_id and marks.markable_type = '$source'
        )
        
        ON DUPLICATE KEY UPDATE
            comprehension = VALUES(comprehension),
            score         = VALUES(score),
            created_at    = VALUES(created_at),
            updated_at    = VALUES(updated_at);
        MYSQL;
    }
}
