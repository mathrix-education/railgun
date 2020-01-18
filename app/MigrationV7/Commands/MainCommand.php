<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use Exception;
use Illuminate\Support\Facades\DB;
use function dirname;
use function file_get_contents;
use function microtime;
use function round;
use function time;

class MainCommand extends V7Command
{
    private const COMMANDS = [
        'v7:reset',
        'v7:zeros',
        'v7:initialization',
        'v7:flashcards',
        'v7:subjects',
        'v7:exercises',
        'v7:courses',
        'v7:notions',
        'v7:comments',
        'v7:posts',
        'v7:exams',
        'v7:products',
        'v7:acquisitions',
        'v7:coupons',
        'v7:interactions',
        'v7:votes',
    ];

    protected $signature = 'v7';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $start = time();
        $this->banner();

        foreach (self::COMMANDS as $command) {
            $now = $this->timeMs();

            try {
                DB::beginTransaction();
                $this->call($command);
                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
                $this->alert("Error while executing command $command");
                throw $exception;
            }

            $duration = $this->timeMs() - $now;

            if ($duration > 10000) {
                $durationSeconds = round($duration / 1000, 1);
                $this->info("> Done in {$durationSeconds} s");
            } else {
                $this->info("> Done in {$duration} ms");
            }
            $this->line('');
        }

        $duration = time() - $start;
        $this->line("> Finished in $duration seconds!");
    }

    /**
     * Print the Mathrix V7.0.0 banner
     */
    private function banner(): void
    {
        $this->output->write(file_get_contents(dirname(__DIR__) . '/banner.txt'));
        $this->line('Migration script by Mathieu Bour and Valentin Pollart');
        $this->line("\n");
    }

    /**
     * Get the current time in milliseconds.
     *
     * @return int
     */
    private function timeMs(): int
    {
        return (int)round(microtime(true) * 1000, 0);
    }
}
