<?php
namespace App\Providers;

use Carbon\Carbon;
use Dusterio\LumenPassport\LumenPassport;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

/**
 * Class AuthServiceProvider.
 * Provide Laravel Passport configuration.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     */
    public function boot()
    {
        // Passport configuration
        Passport::tokensExpireIn(Carbon::now()
            ->addMonth());
        Passport::refreshTokensExpireIn(Carbon::now()
            ->addMonths(2));

        LumenPassport::allowMultipleTokens();

        // Passport tokens definition
        Passport::tokensCan([
            "admin" => "Administrator",
            "moderator" => "Moderator",
            "redactor" => "Redactor",
            "registered" => "Registered",
            "support" => "Support",
        ]);
    }
}
