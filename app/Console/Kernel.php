<?php

namespace App\Console;

use App\Console\Commands\Braintree\NewPayPalCheck;
use App\Console\Commands\CompileMails;
use App\Console\Commands\DBDestroyCommand;
use App\Console\Commands\Pyramid\PyramidCheckCommand;
use App\Console\Commands\Pyramid\PyramidCommand;
use App\Console\Commands\Pyramid\PyramidFixCommand;
use App\Console\Commands\Pyramid\PyramidForeignKeysCommand;
use App\Console\Commands\Pyramid\PyramidMarksActivitiesCommand;
use App\Console\Commands\Pyramid\PyramidMarksCommand;
use App\Console\Commands\Pyramid\PyramidMarksModuleCommand;
use App\Console\Commands\Pyramid\PyramidMigrateCommand;
use App\Console\Commands\Pyramid\PyramidOrphansCommand;
use App\Console\Commands\Pyramid\PyramidPostMigrate;
use App\Console\Commands\Pyramid\PyramidPreMigrateCommand;
use App\Console\Commands\Pyramid\PyramidResetCommand;
use App\Console\Commands\Pyramid\PyramidSEOCommand;
use App\Console\Commands\Sitemap\SitemapAll;
use App\Console\Commands\Sitemap\SitemapContent;
use App\Console\Commands\TestCommand;
use App\Console\Commands\TestRouter;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 * Console Kernel, where we declare commands and cron tasks.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.1
 */
class Kernel extends ConsoleKernel
{
    /** @var array The Artisan commands. */
    protected $commands = [
        TestCommand::class,
        DBDestroyCommand::class,

        NewPayPalCheck::class,
        CompileMails::class,

        // Pyramid commands
        PyramidCommand::class,
        PyramidFixCommand::class,
        PyramidPreMigrateCommand::class,
        PyramidResetCommand::class,
        PyramidMigrateCommand::class,
        PyramidOrphansCommand::class,
        PyramidSEOCommand::class,
        PyramidForeignKeysCommand::class,
        PyramidMarksCommand::class,
        PyramidMarksModuleCommand::class,
        PyramidMarksActivitiesCommand::class,
        PyramidPostMigrate::class,
        PyramidCheckCommand::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        if (app()->environment("master")) {
            $schedule->command("backup:mysql-dump")
                ->dailyAt("02:00");
            $schedule->command("backup:mysql-dump")
                ->dailyAt("12:00");
            $schedule->command("backup:mysql-dump")
                ->dailyAt("17:00");
            $schedule->command("backup:cdn")
                ->dailyAt("02:20");
            $schedule->command("sitemap:all")
                ->dailyAt("02:10");
        }
    }
}
