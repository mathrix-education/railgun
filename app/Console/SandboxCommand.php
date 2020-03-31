<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Railgun\Buster\Pipelines\ExamPipeline;

/**
 * Dummy Command which is only used to test behavioral code.
 */
class SandboxCommand extends Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'sandbox';

    public function handle(ExamPipeline $converter): void
    {
        // Implement sandbox command
    }
}
