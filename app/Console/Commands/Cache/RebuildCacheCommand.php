<?php

namespace App\Console\Commands\Cache;

use App\Console\Commands\BoxStyle;
use App\Models\User;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class RebuildCacheCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc3
 */
class RebuildCacheCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "cache:rebuild";


    public function handle()
    {
        $this->call("cache:clear");

        $this->rebuildBraintreeCustomers();
    }


    public function rebuildBraintreeCustomers()
    {
        $customers = User::query()
            ->whereNotNull("braintree_id")
            ->get();
        $this->progressStart($customers->count());
        $customers->each(function (User $customer) {
            $customer->getBraintreeCustomer();
            $this->progressAdvance();
        });
        $this->progressFinish();
    }
}
