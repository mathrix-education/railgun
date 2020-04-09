<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Railgun\Crypto\PasswordResetCrypto;

class CryptoServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(PasswordResetCrypto::class);
    }

    /**
     * @return string[] The services provided by the provider.
     *
     * @codeCoverageIgnore
     */
    public function provides(): array
    {
        return [PasswordResetCrypto::class];
    }
}
