<?php

namespace App\Console\Commands\Braintree;

use App\Console\Commands\BoxStyle;
use App\Models\User;
use Braintree\Subscription;
use Braintree\SubscriptionSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Class NewPayPalCheck.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class NewPayPalCheck extends Command
{
    use BoxStyle;

    protected $signature = "braintree:new-paypal-check";


    public function handle()
    {
        $customersIds = new Collection();


        $this->line("Retrieving subscriptions");
        $subscriptions = Subscription::search([
            SubscriptionSearch::planId()->is("v4_monthly"),
            SubscriptionSearch::inTrialPeriod()->is(false),
            SubscriptionSearch::status()->is(Subscription::ACTIVE)
        ]);

        /** @var Subscription $subscription */
        $this->progressStart($subscriptions->maximumCount());
        foreach ($subscriptions as $subscription) {
            $lastTransaction = $subscription->transactions[0];

            if (isset($lastTransaction->paypalDetails)) {
                $customersIds->push($lastTransaction->customerDetails->id);
            }
            $this->progressAdvance();
        }
        $this->progressFinish();
        $this->line("Found {$customersIds->count()} PayPal subscriptions");

        $customersIds = $customersIds->unique();
        $this->line("{$customersIds->count()} customers to notify");

        $this->line("Writing in user settings...");
        $this->progressStart($customersIds->count());
        foreach ($customersIds as $customersId) {
            $user = User::query()
                ->where("braintree_id", "=", $customersId)
                ->firstOrFail();
            $settings = $user->settings;
            $settings["need_paypal_refresh"] = true;
            $user->settings = $settings;
            //$user->save();

            $this->progressAdvance();
        }
        $this->progressFinish();

        $this->line("Done.");
    }
}
