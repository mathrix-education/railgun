<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Observers\ActivityObserver;
use App\Models\Observers\CategoryObserver;
use App\Models\Observers\ChapterObserver;
use App\Models\Observers\ClassObserver;
use App\Models\Observers\CommentObserver;
use App\Models\Observers\CountryObserver;
use App\Models\Observers\CourseObserver;
use App\Models\Observers\DisciplineObserver;
use App\Models\Observers\ModuleObserver;
use App\Models\Observers\NotionObserver;
use App\Models\Observers\PostObserver;
use App\Models\Observers\QuoteObserver;
use App\Models\Observers\UserObserver;
use App\Models\Observers\VoteObserver;
use App\Models\Post;
use App\Models\Quote;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Cashier::useCurrency('eur', '€');

        // MorphMap
        Relation::morphMap([
            'activity' => Activity::class,
            'module' => Module::class,
            'post' => Post::class,
            'course' => Course::class,
            'comment' => Comment::class
        ]);

        // Observers
        User::observe(UserObserver::class);
        Class_::observe(ClassObserver::class);
        Country::observe(CountryObserver::class);
        Discipline::observe(DisciplineObserver::class);
        Chapter::observe(ChapterObserver::class);
        Notion::observe(NotionObserver::class);
        Activity::observe(ActivityObserver::class);
        Module::observe(ModuleObserver::class);
        Post::observe(PostObserver::class);
        Course::observe(CourseObserver::class);
        Category::observe(CategoryObserver::class);
        Comment::observe(CommentObserver::class);
        Vote::observe(VoteObserver::class);
        Quote::observe(QuoteObserver::class);
    }
}
