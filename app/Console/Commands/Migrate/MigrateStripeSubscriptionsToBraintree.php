<?php
namespace App\Console\Commands\Migrate;

use App\Models\User;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Subscription;

/**
 * Class MigrateStripeSubscriptionsToBraintree
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Console\Commands\Migrate
 * @since
 * @copyright Mathrix Education SA
 */
class MigrateStripeSubscriptionsToBraintree extends Command
{
    protected $signature = "migrate:stripe:subscriptions:to:braintree";


    /**
     * @throws \Stripe\Error\Api
     */
    public function handle()
    {
        Stripe::setApiKey("sk_live_FclQbgqwPHYA5uFHQIJni5I6");

        // Retrieve
        $stripeIterator = null;
        $subscriptions = [];

        $this->output->writeln("Retrieving subscriptions from Stripe");
        do {
            $stripeSubscriptionsResults = Subscription::all([
                //"limit" => 100,
                "limit" => 10,
                "starting_after" => $stripeIterator,
                "status" => "all"
            ]);
            $data = $stripeSubscriptionsResults->data;
            $stripeIterator = $data[count($data) - 1];

            $subscriptions = array_merge($subscriptions, $data);

            $this->output->write(".");
            break;
        } while ($stripeSubscriptionsResults->has_more);
        $this->output->newLine();
        $subscriptionCount = count($subscriptions);

        $this->info("$subscriptionCount to push to Braintree");

        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $customer = User::where("stripe_id", "=", $subscription->customer)->first();

            if ($customer === null) {
                continue;
            }

            $customer->createBraintreeCustomer();
        }
    }
}
