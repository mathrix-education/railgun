<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidPreMigrateCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class Pyramid02PreMigrateCommand extends BaseCommand
{
    use BoxStyle;

    public const MIGRATE_STOP_BEFORE = "2019_04_07_123307_clean_post_v5";

    protected $signature = "pyramid:pre-migrate";


    public function handle()
    {
        $this->cell("Preparing migration files...");
        $migrationsGlob = database_path("migrations/*.php");
        $postMigrationDir = database_path("post_migrations");
        $migrationStopBefore = database_path("migrations/" . self::MIGRATE_STOP_BEFORE . ".php");

        if (file_exists($postMigrationDir)) {
            $existingPostMigrations = glob($postMigrationDir . "/*.php");

            foreach ($existingPostMigrations as $migrationFile) {
                $filename = basename($migrationFile);
                rename(database_path("post_migrations/$filename"), database_path("migrations/$filename"));
            }

            rmdir($postMigrationDir);
        }

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

        $this->cell(count($migrationFiles) . " files moved", false);
    }
}
