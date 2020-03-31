<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Exception;
use Illuminate\Cache\Repository as CacheImplementation;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Railgun\Console\Kernel;
use Railgun\Handlers\Handler;
use Railgun\Middleware\ImpersonateMiddleware;
use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Lesson;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\User;
use function in_array;

/**
 * Register all application service providers.
 *
 * @property-read Application $app
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance('path.config', $this->app->configPath());

        // Singletons
        $this->app->singleton(CacheImplementation::class, CacheContract::class);
        $this->app->singleton(ExceptionHandler::class, Handler::class);
        $this->app->singleton(KernelContract::class, Kernel::class);
    }

    /**
     * @throws Exception
     */
    public function boot(): void
    {
        // Headers
        $this->app->middleware([ImpersonateMiddleware::class]);

        Gate::define('scope', static function (?User $user, string $scope) {
            return $user !== null && in_array($scope, $user->scopes);
        });

        // MorphMap
        $this->setMorphMap();
    }

    /**
     * Build the model morph map based on the classes in the models namespace.
     */
    private function setMorphMap(): void
    {
        Relation::morphMap([
            /**
             * @see Commentable
             * @see Interactable
             * @see Labelable
             * @see Notable
             * @see Reportable
             */
            'subject'   => Chapter::class,
            'course'    => Course::class,
            'exercise'  => Exercise::class,
            'flashcard' => Flashcard::class,
            'exam'      => Exam::class,
            'lesson'    => Lesson::class,
            /** @see Acquerable */
            'pack'      => Pack::class,
            'training'  => Training::class,
        ]);
    }
}
