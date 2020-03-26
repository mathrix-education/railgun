<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

class TriggersCommand extends V7Command
{
    protected $signature = 'v7:triggers';

    public function handle(): void
    {
        $this->title('Creating triggers');
        $this->migrateTo('2020_01_18_231734_create_comments_triggers');
    }
}
