<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BoxStyle;
use Illuminate\Support\Facades\Artisan;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class Pyramid00Command extends BaseCommand
{
    use BoxStyle;

    protected $signature = "pyramid";


    public function handle()
    {
        $this->line("Pyramid Migrator by Mathieu Bour");

        $startTime = time();
        $this->call("pyramid:fix");
        $this->call("pyramid:pre-migrate");

        $this->line("Migrating...");
        Artisan::call("migrate", ["--quiet" => ""]);

        $this->call("pyramid:migrate");
        $this->call("pyramid:orphans");
        $this->call("pyramid:seo");
        $this->call("pyramid:foreign-keys");
        $this->call("pyramid:marks:activities");
        $this->call("pyramid:marks:modules");
        $this->call("pyramid:marks:stats");
        $this->call("pyramid:check");
        $this->call("pyramid:change-notion-types");
        $this->call("pyramid:post-migrate");

        $this->line("Cleaning old tables");
        Artisan::call("migrate", ["--quiet" => ""]);

        $this->line("Calculating counters...");
        Artisan::call("db:seed", ["--class" => "CountersTableSeeder"]);

        $this->line("Done !");

        $duration = time() - $startTime;
        $seconds = $duration % 60;
        $minutes = (int)($duration - $seconds) / 60;
        $this->line("Migration successful. Time elapsed: $minutes minutes and $seconds seconds");
    }
}
