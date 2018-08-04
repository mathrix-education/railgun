<?php
namespace App\Console;

use App\Console\Commands\Dry;
use App\Console\Commands\Format\FormatCopyrightChecker;
use App\Console\Commands\Make\MakeApidoc;
use App\Console\Commands\Make\MakeTest;
use App\Console\Commands\SetVersion;
use App\Console\Commands\Sitemap\SitemapAll;
use App\Console\Commands\Sitemap\SitemapContent;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Console
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Dry::class,
        FormatCopyrightChecker::class,
        SetVersion::class,
        // make:
        MakeApidoc::class,
        MakeTest::class,
        // sitemap:
        SitemapAll::class,
        SitemapContent::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (app()->environment("master")) {
            $schedule->command("backup:mysql-dump")->dailyAt("02:00");
            $schedule->command("sitemap:all")->dailyAt("02:10");
        }
    }
}
