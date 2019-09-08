<?php

namespace App\Console;

use App\Console\Commands\Cache\RebuildCacheCommand;
use App\Console\Commands\CleanCacheCommand;
use App\Console\Commands\CompileMails;
use App\Console\Commands\TestCommand;
use App\Services\GoogleCloud\KMS\Commands\DecryptCommand;
use App\Services\GoogleCloud\KMS\Commands\EncryptCommand;
use App\Services\GoogleCloud\KMS\Commands\SetupCommand;
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
        EncryptCommand::class,
        DecryptCommand::class,
        SetupCommand::class
    ];


    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(CleanCacheCommand::class)->everyTenMinutes();
        $schedule->command("queue:work --stop-when-empty")->everyMinute();
    }
}
