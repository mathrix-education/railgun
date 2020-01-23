<?php

declare(strict_types=1);

namespace App\Console;

use App\Services\GoogleCloud\KMS\Commands\DecryptCommand;
use App\Services\GoogleCloud\KMS\Commands\EncryptCommand;
use App\Services\GoogleCloud\KMS\Commands\SetupCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Console Kernel, where the application commands are declared commands and cron tasks.
 */
class Kernel extends ConsoleKernel
{
    /** @var string[] The Artisan commands. */
    protected $commands = [
        EncryptCommand::class,
        DecryptCommand::class,
        SetupCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule The Laravel scheduler instance.
     *
     * @codeCoverageIgnore
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cache cleaning every ten minutes
        $schedule->command(CacheCleanCommand::class)
            ->everyTenMinutes();

        // Queue worker every minute
        $schedule->command('queue:work --stop-when-empty')
            ->everyMinute();
    }
}
