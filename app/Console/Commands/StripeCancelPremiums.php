<?php
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Subscription;

/**
 * Class StripeCancelPremiums
 *
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since
 * @copyright Mathrix SA
 */
class StripeCancelPremiums extends Command
{
    protected $signature = "stripe:cancel";


    public function handle()
    {
        Stripe::setApiKey(env("STRIPE_SECRET"));

        // Retrieve
        $stripeIterator = null;
        $subscriptions = [];

        $this->output->writeln("Retrieving subscriptions from Stripe");
        do {
            $stripeSubscriptionsResults = Subscription::all([
                "limit" => 100,
                "starting_after" => $stripeIterator,
                "status" => "trialing",
                "plan" => "v3_annually"
            ]);
            $data = $stripeSubscriptionsResults->data;
            $stripeIterator = $data[count($data) - 1];

            $subscriptions = array_merge($subscriptions, $data);

            $this->output->write(".");
        } while ($stripeSubscriptionsResults->has_more);
        $this->output->newLine();
        $subscriptionCount = count($subscriptions);


        // Filter
        $this->output->writeln("$subscriptionCount subscriptions to filter");

        $filteredSubscriptions = [];
        $firstMay = Carbon::createFromDate(2018, 5, 15);
        $thirtyOneJuly = Carbon::createFromDate(2018, 7, 31);

        $this->output->progressStart($subscriptionCount);
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $created = Carbon::createFromTimestamp($subscription->created);
            $trialEnd = Carbon::createFromTimestamp($subscription->trial_end);

            if (
                $created->greaterThan($firstMay) &&
                $trialEnd->isSameDay($thirtyOneJuly)
            ) {
                $filteredSubscriptions[] = $subscription;
            }

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $filteredSubscriptionsCount = count($filteredSubscriptions);

        // Cancel
        $this->output->writeln("$filteredSubscriptionsCount subscriptions to cancel");

        $this->output->progressStart($filteredSubscriptionsCount);
        /** @var Subscription $filteredSubscription */
        foreach ($filteredSubscriptions as $filteredSubscription) {
            $filteredSubscription->cancel([
                "at_period_end" => true
            ]);
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->output->writeln("Done!");
    }
}
