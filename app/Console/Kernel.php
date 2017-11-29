<?php
namespace App\Console;

use App\Console\Commands\SitemapAll;
use App\Console\Commands\SitemapContent;
use App\Console\Commands\SitemapVideos;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SitemapAll::class,
        SitemapContent::class,
        SitemapVideos::class
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
        //
    }
}