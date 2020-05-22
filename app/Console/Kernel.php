<?php

declare(strict_types=1);

namespace Railgun\Console;

use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Console Kernel, where the application commands are declared commands and cron tasks.
 */
class Kernel extends ConsoleKernel
{
    /**
     * @var string[] The Artisan commands.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $commands = [SandboxCommand::class];
}
