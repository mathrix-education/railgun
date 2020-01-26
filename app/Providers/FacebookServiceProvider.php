<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Facebook\Facebook;
use Illuminate\Support\ServiceProvider;
use function env;

/**
 * Provide the Facebook SDK instance.
 */
class FacebookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            Facebook::class,
            static function () {
                return new Facebook([
                    'app_id'                  => env('FACEBOOK_APP_ID'),
                    'app_secret'              => env('FACEBOOK_APP_SECRET'),
                    'default_graph_version'   => env('FACEBOOK_GRAPH_VERSION', 'v3.3'),
                    'persistent_data_handler' => 'memory', // need to use memory since we cannot use sessions
                ]);
            }
        );
    }
}
