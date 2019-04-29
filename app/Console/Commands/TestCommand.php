<?php

namespace App\Console\Commands;

use App\Models\User;
use Braintree\PayPalAccount;
use Braintree\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class TestCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TestCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "test";


    public function handle()
    {
        $customersTxt = file_get_contents(storage_path("customers.txt"));
        $customers = collect(explode("\n", $customersTxt))
            ->unique()
            ->reject(function ($braintreeId) {
                return empty($braintreeId);
            });

        /** @var Collection|User[] $customers */
        $customers = User::query()
            ->whereIn("braintree_id", $customers)
            ->whereIn("grade_id", [10, 11])
            ->get();

        $paypalAccounts = new \Illuminate\Support\Collection();

        // Retrieve braintree customers
        $this->progressStart($customers->count());
        foreach ($customers as $customer) {
            collect($customer->getBraintreePaymentMethods())->each(function ($paymentMethod) use (&$paypalAccounts) {
                if ($paymentMethod instanceof PayPalAccount) {
                    $paypalAccounts->push($paymentMethod);
                }
            });

            $this->progressAdvance();
        }
        $this->progressFinish();

        $this->line("Found {$paypalAccounts->count()} PayPal accounts");
        $data = $paypalAccounts->filter(function (PayPalAccount $paypalAccount) {
            return count($paypalAccount->subscriptions) === 1 && $paypalAccount->subscriptions[0]->status === Subscription::ACTIVE;
        })
            ->map(function (PayPalAccount $paypalAccount) {
                return [
                    "braintree_id" => $paypalAccount->customerId,
                    "premium_bypass_expires_at" => isset($paypalAccount->subscriptions[0]) ? Carbon::instance($paypalAccount->subscriptions[0]->nextBillingDate)->addDay() : "N/A",
                ];
            });
        $this->table(["braintree_id", "premium_bypass_expires_at"], $data);

        foreach ($data as $customerData) {
            /** @var User $customer */
            $customer = User::query()
                ->where("braintree_id", "=", $customerData["braintree_id"])
                ->firstOrFail();
            $customer->premium_bypass_expires_at = $customerData["premium_bypass_expires_at"];
            $customer->settings = array_merge($customer->settings, ["need_paypal_refresh" => true]);
            $customer->save();
        }

        $this->line("done");

        Artisan::call("cache:clear");

        foreach ($data as $customerData) {
            /** @var User $customer */
            $customer = User::query()
                ->where("braintree_id", "=", $customerData["braintree_id"])
                ->firstOrFail();
            if (!$customer->premium) {
                dd("error");
            }
        }
    }
}
