<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Notifications\Console\NotificationTableCommand;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Console Kernel, where the application commands are declared commands and cron tasks.
 */
class Kernel extends ConsoleKernel
{
    /** @var string[] The Artisan commands. */
    protected $commands = [SandboxCommand::class];
}
