<?php

namespace App\Listeners;

use App\Events\SubscriptionChargedSuccessfullyEvent;
use App\Models\User;
use App\Services\Braintree\Models\Plan;
use GuzzleHttp\Client;

/**
 * Class SubscriptionChargedSuccessfullyListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Listeners
 */
class SubscriptionChargedSuccessfullyListener extends BaseListener
{
    const BASE_URL = "https://www.google-analytics.com/collect";


    public function handle(SubscriptionChargedSuccessfullyEvent $event)
    {
        $customerResultSearch = \Braintree\Customer::search([
            \Braintree_CustomerSearch::paymentMethodToken()
                ->is($event->subscription->paymentMethodToken),
        ]);

        /** @var User $customer */
        $customer = User::where("braintree_id", "=", $customerResultSearch->firstItem()->id)
            ->firstOrFail();
        $plan = Plan::find($event->subscription->planId);

        $payload = [
            "v" => "1",
            "t" => "event",
            "tid" => env("ANALYTICS_ID"),
            "cid" => \Faker\Factory::create()->uuid,
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
        $response = $http->post(self::BASE_URL, [
            "query" => $payload
        ]);
    }
}
