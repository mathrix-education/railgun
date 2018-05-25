<?php
namespace App\Providers;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Discount;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\Quote;
use App\Models\Report;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Training;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ChapterPolicy;
use App\Policies\ClassPolicy;
use App\Policies\CommentPolicy;
use App\Policies\CountryPolicy;
use App\Policies\DisciplinePolicy;
use App\Policies\DiscountPolicy;
use App\Policies\LessonPolicy;
use App\Policies\LevelPolicy;
use App\Policies\ModulePolicy;
use App\Policies\NotionPolicy;
use App\Policies\PostPolicy;
use App\Policies\QuotePolicy;
use App\Policies\ReportPolicy;
use App\Policies\SectionPolicy;
use App\Policies\SettingPolicy;
use App\Policies\TrainingPolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Gate;
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
            'registered' => 'Registered'
        ]);

        // Gates declarations
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Chapter::class, ChapterPolicy::class);
        Gate::policy(Class_::class, ClassPolicy::class);
        Gate::policy(Country::class, CountryPolicy::class);
        Gate::policy(Discipline::class, DisciplinePolicy::class);
        Gate::policy(Discount::class, DiscountPolicy::class);
        Gate::policy(Notion::class, NotionPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Quote::class, QuotePolicy::class);
        Gate::policy(Training::class, TrainingPolicy::class);
        Gate::policy(Section::class, SectionPolicy::class);
        // No policy for logs as it is a system-side process
        Gate::policy(Level::class, LevelPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(Lesson::class, LessonPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);

    }
}
