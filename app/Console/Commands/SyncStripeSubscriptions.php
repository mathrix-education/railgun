<?php
namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Stripe\Stripe;

/**
 * Class StripeSyncSubscriptions
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Console\Commands
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class SyncStripeSubscriptions extends Command
{
    protected $signature = "sync:stripe:subscriptions";


    public function handle()
    {
        Stripe::setApiKey(env("STRIPE_SECRET"));

        $stripeSubscriptions = new Collection();
        $objectIterator = null;

        do {
            $collection = \Stripe\Subscription::all(["limit" => 100, "starting_after" => $objectIterator]);

            foreach ($collection->data as $stripeSubscription) {
                $stripeSubscriptions->push($stripeSubscription);
            }

            $last = end($collection->data);
            $objectIterator = $last["id"];

            $this->output->text(Carbon::createFromTimestamp($last["created"])->toDateString());
        } while ($collection->has_more);

        dd($stripeSubscriptions->count());
    }
}
