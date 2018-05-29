<?php
namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Cashier\Subscription;

/**
 * Class MoneyController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class MoneyController extends BaseController
{
    /**
     * 7twanud8v86umjhp8ujhr4y1nv1z7jh60gn96s9q
     *
     * @return JsonResponse
     */
    public function last()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::orderByDesc("created_at")->first();
        $subscription->makeHidden(["id", "user_id", "stripe_id", "quantity", "trials_ends_at", "ends_at", "updated_at"]);

        return response()->json($subscription);
    }
}
