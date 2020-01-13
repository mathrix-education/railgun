<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use function file_get_contents;
use function time;

class MainCommand extends BaseCommand
{
    private const COMMANDS = [
        'v7:reset',
        'migrate',
        'v7:flashcards',
        'v7:subjects',
        'v7:exercises',
    ];
    protected $signature   = 'v7';

    public function handle(): void
    {
        $this->banner();

        foreach (self::COMMANDS as $command) {
            $now = time();
            $this->call($command);
            $duration = time() - $now;
            $this->info('> Done in ' . $duration . ' second' . ($duration > 1 ? 's' : ''));
            $this->line('');
        }
    }

    public function banner()
    {
        $this->output->write(file_get_contents(__DIR__ . '/banner.txt'));
        $this->line('Migration script by Mathieu Bour and Valentin Pollart');
        $this->line("\n");
    }
}
