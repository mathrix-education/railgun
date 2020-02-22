<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Google_Client;
use Illuminate\Support\ServiceProvider;
use function env;

class GoogleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            Google_Client::class,
            static function () {
                return new Google_Client([
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                ]);
            }
        );
    }
}
