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
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     */
    public function boot()
    {
        // Passport configuration
        Passport::tokensExpireIn(Carbon::now()->addMonth());
        Passport::refreshTokensExpireIn(Carbon::now()->addMonths(2));

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
