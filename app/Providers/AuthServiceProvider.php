<?php

namespace App\Providers;

use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Discipline;
use App\Models\Notion;
use App\Models\Observers\ChapterObserver;
use App\Models\Observers\NotionObserver;
use App\Models\User;
use App\Policies\ClassPolicy;
use App\Policies\DisciplinePolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

/**
 * Class AuthServiceProvider
 * @author Mathrix Team
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     * @return void
     */
    public function boot()
    {
        // Passport configuration
        Passport::tokensExpireIn(Carbon::now()->addMonth());
        Passport::refreshTokensExpireIn(Carbon::now()->addMonths(2));

        // Passport tokens definition
        Passport::tokensCan([
            'admin' => 'Administrator',
            'moderator' => 'Moderator',
            'premium' => 'Premium',
            'registered' => 'Registered'
        ]);

        // Gates declarations
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Class_::class, ClassPolicy::class);
        Gate::policy(Discipline::class, DisciplinePolicy::class);
        Gate::policy(Chapter::class, ChapterObserver::class);
        Gate::policy(Notion::class, NotionObserver::class);
    }
}
