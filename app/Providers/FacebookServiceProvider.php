<?php

declare(strict_types=1);

namespace App\Providers;

use Facebook\Facebook;
use Illuminate\Support\ServiceProvider;
use function env;

class FacebookServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->singleton(Facebook::class, static function () {
            return new Facebook([
                'app_id' => env('FACEBOOK_APP_ID'),
                'app_secret' => env('FACEBOOK_APP_SECRET'),
                'default_graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v3.3'),
            ]);
        });
    }
}
