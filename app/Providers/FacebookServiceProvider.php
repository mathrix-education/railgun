<?php

namespace App\Providers;

use App\Services\Facebook\FacebookGuzzle6HttpClient;
use Facebook\Facebook;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

/**
 * Class FacebookServiceProvider.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.1-rc1
 */
class FacebookServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Facebook::class, function () {
            return new Facebook([
                "app_id" => env("FACEBOOK_APP_ID"),
                "app_secret" => env("FACEBOOK_APP_SECRET"),
                "default_graph_version" => env("FACEBOOK_GRAPH_VERSION", "v3.3"),
                "persistent_data_handler" => "memory"
                //"http_client_handler" => new FacebookGuzzle6HttpClient(new Client()),
            ]);
        });
    }
}
