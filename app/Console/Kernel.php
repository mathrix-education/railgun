<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CacheCleanCommand;
use App\Console\Commands\TestCommand;
use App\MigrationV7\AcquisitionsCommand;
use App\MigrationV7\CommentsCommand;
use App\MigrationV7\CouponsCommand;
use App\MigrationV7\CoursesCommand;
use App\MigrationV7\DatabaseResetCommand;
use App\MigrationV7\ExamsCommand;
use App\MigrationV7\ExercisesCommand;
use App\MigrationV7\FlashcardsCommand;
use App\MigrationV7\InteractionsCommand;
use App\MigrationV7\MainCommand;
use App\MigrationV7\NotionsCommand;
use App\MigrationV7\PostsCommand;
use App\MigrationV7\ProductsCommand;
use App\MigrationV7\SubjectsCommand;
use App\MigrationV7\ZerosCommand;
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
        TestCommand::class,
        CacheCleanCommand::class,
        EncryptCommand::class,
        DecryptCommand::class,
        SetupCommand::class,
        // V7
        MainCommand::class,
        DatabaseResetCommand::class,
        ZerosCommand::class,
        FlashcardsCommand::class,
        SubjectsCommand::class,
        ExercisesCommand::class,
        CoursesCommand::class,
        NotionsCommand::class,
        CommentsCommand::class,
        ExamsCommand::class,
        PostsCommand::class,
        ProductsCommand::class,
        AcquisitionsCommand::class,
        CouponsCommand::class,
        InteractionsCommand::class,
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
