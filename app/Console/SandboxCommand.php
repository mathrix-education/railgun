<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;

/**
 * Dummy Command which is only used to test behavioral code.
 */
class SandboxCommand extends Command
{
    /**
     * @var string $signature
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'sandbox';

    public function handle(): void
    {
        // Body
    }
}
