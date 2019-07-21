<?php

namespace App\Console\Commands\Braintree;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class BraintreeRestoreSubscriptionsCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since
 */
class BraintreeRestoreSubscriptionsCommand extends BraintreeRestoreCommand
{
    protected $signature = "braintree:restore:subscriptions";


    public function handle()
    {
        /** @var Collection $customerIds */
        $customerIds = DB::table("users")
            ->select("braintree_id")
            ->get()
            ->pluck("braintree_id");

        $this->process("subscriptions", "subscriptions", function (Collection $collection) use ($customerIds) {
            return $collection
                ->sortBy("created_at")
                ->filter(function ($subscription) use ($customerIds) {
                    return in_array($subscription["customer_id"], $customerIds->toArray());
                });
        });
    }


    /**
     * @param $record
     *
     * @return array
     * @throws Exception
     */
    protected function parse($record)
    {
        return [
            "id" => $record["Subscription ID"],
            "status" => $record["Status"],
            "price" => floatval($record["Subscription Price"]),
            "next_billing_amount" => floatval($record["Next Billing Period Amount"]),
            "current_billing_cycle" => intval($record["Current Billing Period"]),
            "number_of_billing_cycles" => !empty($record["Number of Billing Cycles"])
                ? intval($record["Number of Billing Cycles"]) : null,
            "billing_period_end_date" => (new Carbon($record["Paid Through Date"])),
            "next_billing_date" => (new Carbon($record["Next Bill Date"])),
            "customer_id" => $record["Customer ID"]
        ];
    }
}
