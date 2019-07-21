<?php

namespace App\Console\Commands\Braintree;

use App\Console\Commands\BoxStyle;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BraintreeRestoreTransactionsCommand extends BraintreeRestoreCommand
{
    use BoxStyle;

    private const PAYMENT_INSTRUMENT_MAP = [
        "PayPal Account" => "paypal_account",
        "Credit Card" => "credit_card"
    ];

    protected $signature = "braintree:restore:transactions";


    public function handle()
    {
        /** @var Collection $customerIds */
        $customerIds = DB::table("users")
            ->select("braintree_id")
            ->get()
            ->pluck("braintree_id");

        /** @var Collection $subscriptionIds */
        $subscriptionIds = DB::table("subscriptions")
            ->select("id")
            ->get()
            ->pluck("id");

        $this->process("transactions", "transactions",
            function (Collection $collection) use ($customerIds, $subscriptionIds) {
                return $collection
                    ->sortBy("created_at")
                    ->filter(function ($transaction) use ($customerIds, $subscriptionIds) {
                        return in_array($transaction["customer_id"], $customerIds->toArray())
                            && (
                                $transaction["subscription_id"] === null
                                || in_array($transaction["subscription_id"], $subscriptionIds->toArray())
                            );
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
        $createdAt = (new Carbon($record["Created Datetime"]));

        return [
            "id" => $record["Transaction ID"],
            "type" => $record["Transaction Type"],
            "amount" => floatval($record["Amount Submitted For Settlement"]),
            "refunded_amount" => 0,
            "currency" => $record["Currency ISO Code"],
            "status" => $record["Transaction Status"],
            "payment_instrument_type" => self::PAYMENT_INSTRUMENT_MAP[$record["Payment Instrument Type"]],
            "processor_response" => json_encode([
                "code" => intval($record["Processor Response Code"]),
                "text" => $record["Processor Response Text"]
            ]),
            "gateway_rejection_reason" => $this->notEmptyOrNull($record["Gateway Rejection Reason"]),
            "refund_transaction_id" => $this->notEmptyOrNull($record["Refunded Transaction ID"]),
            "customer_id" => $record["Customer ID"],
            "subscription_id" => $this->notEmptyOrNull($record["Subscription ID"]),
            "created_at" => $createdAt,
            "updated_at" => $createdAt
        ];
    }
}
