<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidPostMigrate.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class Pyramid11PostMigrate extends BaseCommand
{
    use BoxStyle;

    protected $signature = "pyramid:post-migrate";


    public function handle()
    {
        $this->cell("Preparing post-migration files...");

        $postMigrationFiles = glob(database_path("post_migrations/*.php"));

        foreach ($postMigrationFiles as $postMigrationFile) {
            $filename = basename($postMigrationFile);
            rename(database_path("post_migrations/$filename"), database_path("migrations/$filename"));
        }

        rmdir(database_path("post_migrations"));

        $this->cell(count($postMigrationFiles) . " files moved", false);
    }
}
