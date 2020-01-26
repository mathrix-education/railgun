<?php

declare(strict_types=1);

namespace Railgun\Console;

use Railgun\Services\GoogleCloud\KMS\Commands\DecryptCommand;
use Railgun\Services\GoogleCloud\KMS\Commands\EncryptCommand;
use Railgun\Services\GoogleCloud\KMS\Commands\SetupCommand;
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
}
