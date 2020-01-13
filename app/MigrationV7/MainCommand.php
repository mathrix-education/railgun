<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

class MainCommand extends BaseCommand
{
    protected $signature = 'v7';

    public function handle(): void
    {
        $this->call('v7:reset');
        $this->call('migrate');
        $this->call('v7:flashcards');
    }
}
