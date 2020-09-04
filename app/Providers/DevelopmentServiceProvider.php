<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Support\ServiceProvider;
use Railgun\Console\GCloud\DatabaseSeedCommand;
use Railgun\Console\GCloud\DatabaseSynchronizeCommand;
use Railgun\Console\ReseedCommand;
use Railgun\Console\SandboxCommand;

/**
 * Provide useful development commands.
 */
class DevelopmentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            SandboxCommand::class,
            ReseedCommand::class,
            DatabaseSeedCommand::class,
            DatabaseSynchronizeCommand::class,
        ]);
    }
}
