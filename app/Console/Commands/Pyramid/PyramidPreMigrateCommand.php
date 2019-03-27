<?php

namespace App\Console\Commands\Pyramid;

use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidPreMigrateCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidPreMigrateCommand extends BaseCommand
{
    protected $signature = "pyramid:pre-migrate";
    public const MIGRATE_STOP_BEFORE = "2019_03_25_235918_clean_post_v5";


    public function handle()
    {
        $this->block("Preparing migration files...");
        $migrationsGlob = database_path("migrations/*.php");
        $postMigrationDir = database_path("post_migrations");
        $migrationStopBefore = database_path("migrations/" . self::MIGRATE_STOP_BEFORE . ".php");

        if (!file_exists($postMigrationDir)) {
            mkdir($postMigrationDir);
        }

        $migrationFiles = array_values(glob($migrationsGlob));
        while ($migrationFiles[0] !== $migrationStopBefore) {
            array_shift($migrationFiles);
        }

        foreach ($migrationFiles as $migrationFile) {
            $filename = basename($migrationFile);
            rename(database_path("migrations/$filename"), database_path("post_migrations/$filename"));
        }

        $this->success(count($migrationFiles) . " files moved");
    }
}
