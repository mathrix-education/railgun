<?php

declare(strict_types=1);

namespace Railgun\MigrationV7;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Railgun\MigrationV7\Commands\AcquisitionsCommand;
use Railgun\MigrationV7\Commands\CleanCommand;
use Railgun\MigrationV7\Commands\CommentsCommand;
use Railgun\MigrationV7\Commands\CouponsCommand;
use Railgun\MigrationV7\Commands\CoursesCommand;
use Railgun\MigrationV7\Commands\ExamsCommand;
use Railgun\MigrationV7\Commands\ExercisesCommand;
use Railgun\MigrationV7\Commands\FlashcardsCommand;
use Railgun\MigrationV7\Commands\InitializationCommand;
use Railgun\MigrationV7\Commands\InteractionsCommand;
use Railgun\MigrationV7\Commands\MigrateCommand;
use Railgun\MigrationV7\Commands\PostsCommand;
use Railgun\MigrationV7\Commands\ProductsCommand;
use Railgun\MigrationV7\Commands\ReportsCommand;
use Railgun\MigrationV7\Commands\ResetCommand;
use Railgun\MigrationV7\Commands\SubjectsCommand;
use Railgun\MigrationV7\Commands\SubscriptionsCommand;
use Railgun\MigrationV7\Commands\TriggersCommand;
use Railgun\MigrationV7\Commands\VotesCommand;
use Railgun\MigrationV7\Commands\ZerosCommand;

/**
 * @property-read Application $app
 */
class MigrationV7ServiceProvider extends ServiceProvider
{
    public function register()
    {
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
            TriggersCommand::class,
            SubscriptionsCommand::class,
        ]);
    }
}
