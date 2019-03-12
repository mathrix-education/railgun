<?php

namespace App\Providers;

use Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

/**
 * Class GoogleDriveServiceProvider.
 *
 * @author             Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author             Jérémie Levain <munezero999@live.fr>
 * @since              1.0.1
 * @copyright          Mathrix Education SA
 * @package            App\Providers
 * @codeCoverageIgnore
 */
class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Storage::extend("google", function ($app, $config) {
            $client = new \Google_Client();
            $client->setClientId($config["clientId"]);
            $client->setClientSecret($config["clientSecret"]);
            $client->refreshToken($config["refreshToken"]);
            $service = new \Google_Service_Drive($client);
            $adapter = new GoogleDriveAdapter($service, $config["folderId"]);

            return new Filesystem($adapter);
        });
    }


    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
