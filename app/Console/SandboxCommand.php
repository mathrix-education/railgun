<?php

declare(strict_types=1);

namespace Railgun\Console;

use Braintree\Gateway;
use Illuminate\Console\Command;
use function dump;
use function round;

/**
 * Dummy Command which is only used to test behavioral code.
 *
 * @codeCoverageIgnore
 */
class SandboxCommand extends Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'sandbox';

    public function handle(Gateway $client): void
    {
        // Implement sandbox command
        $str = '17.90';
        dump((int)round((float)$str * 100));
    }
}
