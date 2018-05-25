<?php
namespace App\Console;

use App\Console\Commands\MailchimpUpdate;
use App\Console\Commands\MakeApidoc;
use App\Console\Commands\SitemapAll;
use App\Console\Commands\SitemapContent;
use App\Console\Commands\SyncStripeSubscriptions;
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
        MailchimpUpdate::class,
        MakeApidoc::class,
        SitemapAll::class,
        SitemapContent::class,
        SyncStripeSubscriptions::class
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
