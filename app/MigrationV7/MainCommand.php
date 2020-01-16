<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Exception;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use function file_get_contents;
use function time;

class MainCommand extends BaseCommand
{
    private const COMMANDS = [
        'v7:reset',
        'v7:zeros',
        'migrate',
        'v7:flashcards',
        'v7:subjects',
        'v7:exercises',
        'v7:courses',
        'v7:notions',
        'v7:comments',
        'v7:exams',
        'v7:products',
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
            $now = time();

            try {
                DB::beginTransaction();
                $this->call($command);
                DB::commit();
            } catch (Exception $exception) {
                DB::rollBack();
                $this->alert("Error while executing command $command");
                throw $exception;
            }

            $duration = time() - $now;
            $this->info('> Done in ' . $duration . ' second' . ($duration > 1 ? 's' : ''));
            $this->line('');
        }
    }

    public function banner(): void
    {
        $this->output->write(file_get_contents(__DIR__ . '/banner.txt'));
        $this->line('Migration script by Mathieu Bour and Valentin Pollart');
        $this->line("\n");
    }
}
