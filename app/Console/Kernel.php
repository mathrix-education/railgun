<?php

namespace App\Console;

use App\Console\Commands\Braintree\NewPayPalCheck;
use App\Console\Commands\CompileMails;
use App\Console\Commands\DBDestroyCommand;
use App\Console\Commands\Pyramid\Pyramid03MigrateCommand;
use App\Console\Commands\Pyramid\Pyramid10CheckCommand;
use App\Console\Commands\Pyramid\Pyramid00Command;
use App\Console\Commands\Pyramid\Pyramid01FixCommand;
use App\Console\Commands\Pyramid\Pyramid06ForeignKeysCommand;
use App\Console\Commands\Pyramid\Pyramid07MarksActivitiesCommand;
use App\Console\Commands\Pyramid\Pyramid09MarksStatsCommand;
use App\Console\Commands\Pyramid\Pyramid08MarksModuleCommand;
use App\Console\Commands\Pyramid\PyramidMigrateCommand;
use App\Console\Commands\Pyramid\Pyramid04OrphansCommand;
use App\Console\Commands\Pyramid\Pyramid11PostMigrate;
use App\Console\Commands\Pyramid\Pyramid02PreMigrateCommand;
use App\Console\Commands\Pyramid\PyramidResetCommand;
use App\Console\Commands\Pyramid\Pyramid05SEOCommand;
use App\Console\Commands\TestCommand;
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
        Pyramid00Command::class,
        Pyramid01FixCommand::class,
        Pyramid02PreMigrateCommand::class,
        Pyramid03MigrateCommand::class,
        Pyramid04OrphansCommand::class,
        Pyramid05SEOCommand::class,
        Pyramid06ForeignKeysCommand::class,
        Pyramid09MarksStatsCommand::class,
        Pyramid08MarksModuleCommand::class,
        Pyramid07MarksActivitiesCommand::class,
        Pyramid11PostMigrate::class,
        Pyramid10CheckCommand::class,
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
