<?php

declare(strict_types=1);

namespace App\MigrationV7;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 *
 */
class NotionsCommand extends V7Command
{
    protected $signature = 'v7:notions';

    public function handle(): void
    {
        $this->info('Analysing notions');
    }
}
