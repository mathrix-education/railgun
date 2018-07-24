<?php
namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Subscription;

/**
 * Class MailchimpSyncFromStripe
 *
 * @author Mathrix Back-End Team
 * @package App\Console\Commands
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class MailchimpSyncFromStripe extends Command
{
    protected $signature = "mailchimp:sync:from:stripe";


    public function handle()
    {
        Stripe::setApiKey(env("STRIPE_SECRET"));
        $stripeIterator = null;
        $subscriptions = [];

        do {
            $stripeSubscriptionsResults = Subscription::all([
                "limit" => 100,
                "starting_after" => $stripeIterator,
                "status" => "all"
            ]);
            $data = $stripeSubscriptionsResults->data;
            $stripeIterator = $data[count($data) - 1];

            $subscriptions = array_merge($subscriptions, $data);

            $this->output->writeln("Current count " . count($subscriptions));
        } while ($stripeSubscriptionsResults->has_more);
        $subscriptionCount = count($subscriptions);

        $this->output->writeln("$subscriptionCount to process");

        $lines = [];

        $this->output->progressStart($subscriptionCount);

        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $user = User::where("stripe_id", "=", $subscription->customer)->first();

            if ($user instanceof User && $user->email !== null) {
                $lines[] = implode(",", [
                    "EMAIL" => $user->email,
                    "STATUS" => $subscription->status,
                    "PLANID" => $subscription->plan->id,
                    "FNAME" => $user->firstname ?? "",
                    "LNAME" => $user->lastname ?? "",
                    "USERNAME" => $user->username ?? "",
                    "CLASS" => $user->class_->name,
                    "STARTDATE" => Carbon::createFromTimestamp($subscription->created)->format("d/m/Y"),
                    "TRIALENDDA" => $subscription->trial_end !== null ? Carbon::createFromTimestamp
                    ($subscription->trial_end)->format("d/m/Y") : "",
                    "CANCELDATE" => $subscription->canceled_at !== null ? Carbon::createFromTimestamp
                    ($subscription->canceled_at)->format("d/m/Y") : "",
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $head = "EMAIL,STATUS,PLANID,FNAME,LNAME,USERNAME,CLASS,STARTDATE,TRIALENDDA,CANCELDATE\n";
        $data = $head . implode("\n", $lines);
        file_put_contents(storage_path() . "/emails.csv", $data);
    }
}
