<?php

declare(strict_types=1);

namespace Railgun\Providers;

use Exception;
use Illuminate\Cache\Repository as CacheImplementation;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Mathrix\Prometheus\Middleware\PrometheusMiddleware;
use Railgun\Console\Kernel;
use Railgun\Handlers\ExceptionPipeline;
use Railgun\Handlers\Handler;
use Railgun\Middleware\CorrelationMiddleware;
use Railgun\Middleware\ImpersonateMiddleware;
use Railgun\Middleware\LanguageMiddleware;
use Railgun\Middleware\TeardownMiddleware;
use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Lesson;
use Railgun\Models\Pack;
use Railgun\Models\Subject;
use Railgun\Models\Training;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Support\PHPPMStatus;
use function array_map;

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
        $this->app->singleton(ExceptionPipeline::class);
        $this->app->singleton(PHPPMStatus::class);
    }

    /**
     * @throws Exception
     */
    public function boot(): void
    {
        // Middleware
        $middleware = [
            TeardownMiddleware::class,
            LanguageMiddleware::class,
            CorrelationMiddleware::class,
            PrometheusMiddleware::class,
            ImpersonateMiddleware::class,
        ];

        array_map(fn($m) => $this->app->singleton($m), $middleware);
        $this->app->middleware($middleware);

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
            'chapter'   => Chapter::class,
            'subject'   => Subject::class,
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
