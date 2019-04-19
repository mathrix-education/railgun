<?php

namespace App\Listeners;

use App\Events\SubscriptionChargedSuccessfullyEvent;
use App\Models\User;
use App\Services\Braintree\Models\Plan;
use Braintree\Customer;
use Braintree\CustomerSearch;
use Faker\Factory as FakerFactory;
use GuzzleHttp\Client;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class SubscriptionChargedSuccessfullyListener.
 * Not tested, since we do not want to interfere with existing statistics.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 *
 * @codeCoverageIgnore
 */
class SubscriptionChargedSuccessfullyListener extends BaseListener
{
    const BASE_URL = "https://www.google-analytics.com/collect";


    /**
     * Handle SubscriptionChargedSuccessfullyEvent.
     *
     * @param SubscriptionChargedSuccessfullyEvent $event
     */
    public function handle(SubscriptionChargedSuccessfullyEvent $event)
    {
        /** @var array|string $query */
        $query = [
            CustomerSearch::paymentMethodToken()->is($event->subscription->paymentMethodToken),
        ];
        $customerResultSearch = Customer::search($query);

        /** @var User $customer */
        $customer = User::query()
            ->where("braintree_id", "=", $customerResultSearch->firstItem()->id)
            ->firstOrFail();
        $plan = Plan::find($event->subscription->planId);

        $payload = [
            "v" => "1",
            "t" => "event",
            "tid" => env("ANALYTICS_ID"),
            "cid" => FakerFactory::create()->uuid,
            "ec" => "Ecommerce",
            "ea" => "Purchase",
            "ni" => "1",
            "ti" => $event->subscription->id,
            "pa" => "purchase",
            "uid" => $customer->id,
            "tr" => $event->transaction->amount,
            "tt" => "0",
            "ts" => "0",
            "pr1nm" => $plan->name,
            "pr1pr" => $plan->price,
            "pr1br" => "Mathrix",
            "pr1ca" => "Premium Plans",
            "pr1va" => str_replace("v4_", "", $plan->id),
            "pr1qt" => "1"
        ];

        $http = new Client();
        $http->post(self::BASE_URL, [
            "query" => $payload
        ]);
    }
}
