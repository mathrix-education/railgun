<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Google_Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use function env;

/**
 * Register the {@see Google_Client}, used for Google authentication.
 */
class GoogleServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(Google_Client::class, static function () {
            return new Google_Client([
                'client_id' => env('GOOGLE_CLIENT_ID'),
            ]);
        });
    }

    public function provides(): array
    {
        return [Google_Client::class];
    }
}
