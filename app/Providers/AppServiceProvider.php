<?php

namespace App\Providers;

use App\Models\Observers\UserObserver;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        User::observe(UserObserver::class);
    }
}
