<?php

namespace App\Listeners;

use App\Events\SubscriptionChargedSuccessfullyEvent;
use App\Models\User;
use App\Services\Braintree\Models\Plan;
use Braintree\Customer;
use Faker\Factory as FakerFactory;
use GuzzleHttp\Client;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class SubscriptionChargedSuccessfullyListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class SubscriptionChargedSuccessfullyListener extends BaseListener
{
    const BASE_URL = "https://www.google-analytics.com/collect";


    public function handle(SubscriptionChargedSuccessfullyEvent $event)
    {
        $customerResultSearch = Customer::search([
            \Braintree_CustomerSearch::paymentMethodToken()
                ->is($event->subscription->paymentMethodToken),
        ]);

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
