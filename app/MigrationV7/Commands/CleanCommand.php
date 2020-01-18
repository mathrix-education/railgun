<?php

declare(strict_types=1);

namespace App\MigrationV7\Commands;

use function copy;
use function database_path;
use function dirname;
use function file_exists;
use function unlink;

class CleanCommand extends V7Command
{
    private const MIGRATION_NAMES = ['2020_01_17_140706_drop_mapping_columns'];

    protected $signature = 'v7:clean';

    public function handle(): void
    {
        foreach (self::MIGRATION_NAMES as $migration) {
            $source      = dirname(__DIR__) . "/Migrations/{$migration}.php";
            $destination = database_path("migrations/{$migration}.php");

            if (file_exists($destination)) {
                unlink($destination);
            }

            copy($source, $destination);
        }

        $this->call('migrate');
    }
}
