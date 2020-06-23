<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Railgun\Crypto\AppleSigninCrypto;
use Windy\Guardian\Crypto\Key;
use Windy\Guardian\Crypto\KeysRegistry;
use Windy\Guardian\Guardian;

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

    public function handle(AppleSigninCrypto $crypto): void
    {
        dd($crypto->getSecret());
    }
}
