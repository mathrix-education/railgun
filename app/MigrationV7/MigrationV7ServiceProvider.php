<?php

declare(strict_types=1);

namespace App\MigrationV7;

use App\MigrationV7\Commands\AcquisitionsCommand;
use App\MigrationV7\Commands\CleanCommand;
use App\MigrationV7\Commands\CommentsCommand;
use App\MigrationV7\Commands\CouponsCommand;
use App\MigrationV7\Commands\CoursesCommand;
use App\MigrationV7\Commands\RestoreCommand;
use App\MigrationV7\Commands\ResetCommand;
use App\MigrationV7\Commands\ExamsCommand;
use App\MigrationV7\Commands\ExercisesCommand;
use App\MigrationV7\Commands\FlashcardsCommand;
use App\MigrationV7\Commands\InitializationCommand;
use App\MigrationV7\Commands\InteractionsCommand;
use App\MigrationV7\Commands\MigrateCommand;
use App\MigrationV7\Commands\NotionsCommand;
use App\MigrationV7\Commands\PostsCommand;
use App\MigrationV7\Commands\ProductsCommand;
use App\MigrationV7\Commands\ReportsCommand;
use App\MigrationV7\Commands\SubjectsCommand;
use App\MigrationV7\Commands\VotesCommand;
use App\MigrationV7\Commands\ZerosCommand;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;

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
            NotionsCommand::class,
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
            RestoreCommand::class,
        ]);
    }
}
