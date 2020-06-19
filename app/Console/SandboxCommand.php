<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Stripe\StripeClient;

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

    public function handle(StripeClient $stripe): void
    {
        dd($stripe);
    }
}
