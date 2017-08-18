<?php

namespace App\Providers;

use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Notion;
use App\Models\Observers\ChapterObserver;
use App\Models\Observers\ClassObserver;
use App\Models\Observers\CountryObserver;
use App\Models\Observers\DisciplineObserver;
use App\Models\Observers\NotionObserver;
use App\Models\Observers\UserObserver;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        User::observe(UserObserver::class);
        Class_::observe(ClassObserver::class);
        Country::observe(CountryObserver::class);
        Discipline::observe(DisciplineObserver::class);
        Chapter::observe(ChapterObserver::class);
        Notion::observe(NotionObserver::class);
    }
}
