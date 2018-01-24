<?php
namespace App\Controllers;

use Laravel\Cashier\Subscription;

class MoneyController extends BaseController
{
    public function last() {
        /** @var Subscription $subscription */
        $subscription = Subscription::orderByDesc("created_at")->first();
        $subscription->makeHidden(["id", "user_id", "stripe_id", "quantity", "trials_ends_at", "ends_at", "updated_at"]);

        return response()->json($subscription);
    }
}
