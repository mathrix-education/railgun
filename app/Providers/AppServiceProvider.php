<?php
namespace App\Providers;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\Section;
use App\Models\Training;
use App\Models\User;
use App\Models\Vote;
use App\Observers\ActivityObserver;
use App\Observers\CategoryObserver;
use App\Observers\ChapterObserver;
use App\Observers\ClassObserver;
use App\Observers\CommentObserver;
use App\Observers\CountryObserver;
use App\Observers\DisciplineObserver;
use App\Observers\LessonObserver;
use App\Observers\ModuleObserver;
use App\Observers\NotionObserver;
use App\Observers\PostObserver;
use App\Observers\SectionObserver;
use App\Observers\TrainingObserver;
use App\Observers\UserObserver;
use App\Observers\VoteObserver;
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
            'notion' => Notion::class,
            'module' => Module::class,
            'post' => Post::class,
            'training' => Training::class,
            'section' => Section::class,
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
        Category::observe(CategoryObserver::class);
        Comment::observe(CommentObserver::class);
        Vote::observe(VoteObserver::class);
        Training::observe(TrainingObserver::class);
        Section::observe(SectionObserver::class);
        Lesson::observe(LessonObserver::class);
    }
}
