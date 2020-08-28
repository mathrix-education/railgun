<?php

declare(strict_types=1);

namespace Railgun\Console;

use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Railgun\Console\GCloud\DatabaseSeedCommand;
use Railgun\Console\GCloud\DatabaseSynchronizeCommand;

/**
 * Console Kernel, where the application commands are declared commands and cron tasks.
 */
class Kernel extends ConsoleKernel
{
    /**
     * @var string[] The Artisan commands.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $commands = [
        SandboxCommand::class,
        ReseedCommand::class,
        DatabaseSynchronizeCommand::class,
        DatabaseSeedCommand::class,
    ];
}
