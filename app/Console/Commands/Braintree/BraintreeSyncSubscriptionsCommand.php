<?php

namespace App\Console\Commands\Braintree;

use App\Console\Commands\BoxStyle;
use App\Services\Braintree\Models\Subscription;
use Braintree\ResourceCollection;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\SubscriptionSearch;
use Carbon\Carbon;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Class BraintreeSyncSubscriptions.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 5.2.0
 */
class BraintreeSyncSubscriptionsCommand extends BaseCommand
{
    use BoxStyle;

    protected $signature = "braintree:sync:subscriptions";


    public function handle()
    {
        $iterator = Carbon::createFromDate(2018, 1, 1);

        while ($iterator->isPast()) {
            $collection = gateway()->subscription()->search([
                SubscriptionSearch::createdAt()->between(
                    $iterator,
                    $iterator->clone()->addYear()
                )
            ]);

            $this->line("Syncing subscriptions created in {$iterator->year}");
            $this->processCollection($collection);

            $iterator = $iterator->addYear();
        }
    }


    private function processCollection(ResourceCollection $collection)
    {
        $this->progressStart($collection->maximumCount());

        /**
         * @var BraintreeSubscription $braintreeSubscription
         */
        foreach ($collection as $braintreeSubscription) {
            /** @var Subscription $subscription */
            $subscription = Subscription::query()
                ->where("id", "=", $braintreeSubscription->id)
                ->first();

            if ($subscription === null) {
                Subscription::createFromBraintreeSubscription($braintreeSubscription);
            } else {
                $subscription->fill(Subscription::toEloquent($braintreeSubscription, $subscription->user));
                $subscription->save();
            }

            $this->progressAdvance();
        }

        $this->progressFinish();
    }
}
