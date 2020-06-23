<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Google_Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use function config;

/**
 * Register the {@see Google_Client}, used for Google authentication.
 */
class GoogleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(Google_Client::class, static function () {
            return new Google_Client([
                'client_id' => config('social.google.client_id'),
            ]);
        });
    }

    /**
     * @return string[] The services provided by the provider.
     *
     * @codeCoverageIgnore
     */
    public function provides(): array
    {
        return [Google_Client::class];
    }
}
