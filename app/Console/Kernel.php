<?php
namespace App\Console;

use App\Console\Commands\BackupCdn;
use App\Console\Commands\MailchimpSyncFromStripe;
use App\Console\Commands\MakeApidoc;
use App\Console\Commands\MakeTest;
use App\Console\Commands\Sitemap\SitemapAll;
use App\Console\Commands\Sitemap\SitemapContent;
use App\Console\Commands\StripeCancelPremiums;
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
        MakeApidoc::class,
        MakeTest::class,
        SitemapAll::class,
        SitemapContent::class,
        SyncStripeSubscriptions::class,
        MailchimpSyncFromStripe::class,
        StripeCancelPremiums::class,
        BackupCdn::class
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
            $schedule->command("backup:mysql-dump")->dailyAt("12:00");
            $schedule->command("backup:mysql-dump")->dailyAt("17:00");
            $schedule->command("backup:cdn")->dailyAt("02:20");
            $schedule->command("sitemap:all")->dailyAt("02:10");
        }
    }
}
