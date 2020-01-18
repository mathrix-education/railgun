<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

class InitializationCommand extends V7Command
{
    protected $signature = 'v7:initialization';

    public function handle(): void
    {
        $this->migrateTo('2020_01_17_040959_create_interactions_table');
    }
}
