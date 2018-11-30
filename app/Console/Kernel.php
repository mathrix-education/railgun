<?php

namespace App\Console;

use App\Console\Commands\Backup\BackupCdn;
use App\Console\Commands\Braintree\BraintreeCheck;
use App\Console\Commands\Clean;
use App\Console\Commands\Clear\ClearCache;
use App\Console\Commands\Dry;
use App\Console\Commands\Format\FormatCheckEnvironments;
use App\Console\Commands\Format\FormatCopyrightChecker;
use App\Console\Commands\Format\FormatHarmonize;
use App\Console\Commands\Make\MakeApidoc;
use App\Console\Commands\Make\MakeTest;
use App\Console\Commands\Redoc\RedocBundle;
use App\Console\Commands\Redoc\RedocServe;
use App\Console\Commands\Road;
use App\Console\Commands\SEO\GenerateIndex;
use App\Console\Commands\Version;
use App\Console\Commands\Sitemap\SitemapAll;
use App\Console\Commands\Sitemap\SitemapContent;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
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
        Clean::class,
        Dry::class,
        Version::class,
        Road::class,
        // backup:
        BackupCdn::class,
        // braintree
        BraintreeCheck::class,
        // clear
        ClearCache::class,
        // format:
        FormatCopyrightChecker::class,
        FormatCheckEnvironments::class,
        FormatHarmonize::class,
        // make:
        MakeApidoc::class,
        MakeTest::class,
        // redoc:
        RedocServe::class,
        RedocBundle::class,
        // seo:
        GenerateIndex::class,
        // sitemap:
        SitemapAll::class,
        SitemapContent::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
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
