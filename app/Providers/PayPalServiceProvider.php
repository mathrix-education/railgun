<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use PayPal\Core\PayPalHttpClient;
use PayPal\Core\ProductionEnvironment;
use PayPal\Core\SandboxEnvironment;
use Tests\PayPal\Mocks\PayPalHttpClientMock;

/**
 * Class PayPalServiceProvider
 * Used to register PayPal SDK
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Providers
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class PayPalServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }


    public function register()
    {
        $this->app->singleton(PayPalHttpClient::class, function () {
            // Build the PayPalHttpClient based on the mode set in the .env
            $mode = env("PAYPAL_MODE");
            $environment = null;

            if ($mode == "sandbox") {
                $environment = new SandboxEnvironment(env("PAYPAL_CLIENT_ID", ""), env("PAYPAL_CLIENT_SECRET", ""));
            } else if ($mode == "live") {
                $environment = new ProductionEnvironment(env("PAYPAL_CLIENT_ID", ""), env("PAYPAL_CLIENT_SECRET", ""));
            } else {
                throw new \Exception("Allowed PayPal modes: sandbox|live");
            }

            if ($this->app->environment() === "testing") {
                return new PayPalHttpClientMock($environment);
            } else {
                return new PayPalHttpClient($environment);
            }
        });
    }
}
