<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Facebook\Facebook;
use Illuminate\Support\ServiceProvider;
use function env;

/**
 * Register Facebook SDK.
 */
class FacebookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Facebook::class, static function () {
            return new Facebook([
                'app_id'     => env('FACEBOOK_APP_ID'),
                'app_secret' => env('FACEBOOK_APP_SECRET'),
            ]);
        });
    }
}
