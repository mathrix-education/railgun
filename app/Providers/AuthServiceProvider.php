<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     * @return void
     */
    public function boot()
    {
        Passport::tokensExpireIn(Carbon::now()->addMonth());
        Passport::refreshTokensExpireIn(Carbon::now()->addMonths(2));

        Passport::tokensCan([
            'admin' => 'Administrator',
            'moderator' => 'Moderator',
            'premium' => 'Premium',
            'registered' => 'Registered'
        ]);

        Gate::policy(User::class, UserPolicy::class);
    }
}
