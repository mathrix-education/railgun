<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Railgun\Auth\ProductGate;

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

    public function handle(): void
    {
        $iterations = 1000;
        $total      = 0;

        $this->getOutput()->progressStart($iterations);

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            Cache::remember('zzzz', 10, static fn() => ProductGate::getUserOwnedProductsGlobalIds(126053));
            $total += microtime(true) - $start;
            $this->getOutput()->progressAdvance();
        }

        $this->getOutput()->progressFinish();

        dd($total / $iterations * 1000);
    }
}
