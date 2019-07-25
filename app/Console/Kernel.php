<?php

namespace App\Console;

use App\Console\Commands\Braintree\BraintreeRestoreCommand;
use App\Console\Commands\Braintree\BraintreeRestoreCustomersCommand;
use App\Console\Commands\Braintree\BraintreeRestoreLineItemsCommand;
use App\Console\Commands\Braintree\BraintreeRestoreSubscriptionsCommand;
use App\Console\Commands\Braintree\BraintreeRestoreTransactionsCommand;
use App\Console\Commands\Braintree\BraintreeSyncCommand;
use App\Console\Commands\Braintree\BraintreeSyncCustomersCommand;
use App\Console\Commands\Braintree\BraintreeSyncPurchasesCommand;
use App\Console\Commands\Braintree\BraintreeSyncSubscriptionsCommand;
use App\Console\Commands\Braintree\BraintreeSyncTransactionsCommand;
use App\Console\Commands\Cache\RebuildCacheCommand;
use App\Console\Commands\CompileMails;
use App\Console\Commands\TestCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 * Console Kernel, where we declare commands and cron tasks.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.1
 */
class Kernel extends ConsoleKernel
{
    /** @var array The Artisan commands. */
    protected $commands = [
        TestCommand::class,
        CompileMails::class,
        RebuildCacheCommand::class,
        BraintreeRestoreCommand::class,
        BraintreeRestoreCustomersCommand::class,
        BraintreeRestoreSubscriptionsCommand::class,
        BraintreeRestoreTransactionsCommand::class,
        BraintreeRestoreLineItemsCommand::class
    ];


    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {

    }
}
