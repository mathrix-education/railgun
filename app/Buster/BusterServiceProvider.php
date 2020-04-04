<?php

declare(strict_types=1);

namespace Railgun\Buster;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Railgun\Buster\Commands\AcquisitionsCommand;
use Railgun\Buster\Commands\CleanCommand;
use Railgun\Buster\Commands\CommentsCommand;
use Railgun\Buster\Commands\CountersCommand;
use Railgun\Buster\Commands\CouponsCommand;
use Railgun\Buster\Commands\CoursesCommand;
use Railgun\Buster\Commands\ExamsCommand;
use Railgun\Buster\Commands\ExercisesCommand;
use Railgun\Buster\Commands\FlashcardsCommand;
use Railgun\Buster\Commands\InitializationCommand;
use Railgun\Buster\Commands\InteractionsCommand;
use Railgun\Buster\Commands\MigrateCommand;
use Railgun\Buster\Commands\OrdersCommand;
use Railgun\Buster\Commands\PostsCommand;
use Railgun\Buster\Commands\ProductsCommand;
use Railgun\Buster\Commands\ReportsCommand;
use Railgun\Buster\Commands\ResetCommand;
use Railgun\Buster\Commands\SessionsCommand;
use Railgun\Buster\Commands\SubjectsCommand;
use Railgun\Buster\Commands\SubscriptionsCommand;
use Railgun\Buster\Commands\UsersCommand;
use Railgun\Buster\Commands\VotesCommand;
use Railgun\Buster\Commands\ZerosCommand;
use Railgun\Buster\Pipelines\ExamPipeline;
use Railgun\Buster\Pipelines\ExercisePipeline;
use Railgun\Buster\Pipelines\FlashcardPipeline;
use Railgun\Buster\Silicate\SilicateConverter;
use function storage_path;

/**
 * @property-read Application $app
 */
class BusterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SilicateConverter::class);
        $this->app->singleton(ExamPipeline::class);
        $this->app->singleton(ExercisePipeline::class);
        $this->app->singleton(FlashcardPipeline::class);

        $this->commands([
            MigrateCommand::class,
            ResetCommand::class,
            ZerosCommand::class,
            InitializationCommand::class,
            FlashcardsCommand::class,
            SubjectsCommand::class,
            ExercisesCommand::class,
            CoursesCommand::class,
            CommentsCommand::class,
            ExamsCommand::class,
            PostsCommand::class,
            ProductsCommand::class,
            AcquisitionsCommand::class,
            CouponsCommand::class,
            InteractionsCommand::class,
            VotesCommand::class,
            ReportsCommand::class,
            CleanCommand::class,
            SubscriptionsCommand::class,
            UsersCommand::class,
            SessionsCommand::class,
            CountersCommand::class,
            OrdersCommand::class,
        ]);
    }

    public function boot(): void
    {
        $this->app['config']->set('logging.channels.buster', [
            'driver' => 'single',
            'path'   => storage_path('buster/report.log'),
            'level'  => 'debug',
        ]);

        $this->app['config']->set('cache.stores.buster', [
            'driver' => 'file',
            'path'   => storage_path('buster/cache/data'),
        ]);
    }
}
