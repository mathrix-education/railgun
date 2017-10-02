<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\Quote;
use App\Models\User;
use App\Models\Vote;
use App\Policies\ActivityPolicy;
use App\Policies\ChapterPolicy;
use App\Policies\ClassPolicy;
use App\Policies\CommentPolicy;
use App\Policies\CountryPolicy;
use App\Policies\CoursePolicy;
use App\Policies\DisciplinePolicy;
use App\Policies\ModulePolicy;
use App\Policies\NotionPolicy;
use App\Policies\PostPolicy;
use App\Policies\QuotePolicy;
use App\Policies\UserPolicy;
use App\Policies\VotePolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

/**
 * Class AuthServiceProvider
 *
 * @author Mathrix Team
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
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
        Gate::policy(Country::class, CountryPolicy::class);
        Gate::policy(Class_::class, ClassPolicy::class);
        Gate::policy(Discipline::class, DisciplinePolicy::class);
        Gate::policy(Chapter::class, ChapterPolicy::class);
        Gate::policy(Notion::class, NotionPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Vote::class, VotePolicy::class);
        Gate::policy(Quote::class, QuotePolicy::class);
    }
}
