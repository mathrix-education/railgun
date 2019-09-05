<?php

namespace App\Controllers;

use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Class PlansController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class PlansController extends BaseController
{
    /**
     * GET /plans
     * Get the available plans.
     *
     * @return SuccessJsonResponse
     */
    public function all(): SuccessJsonResponse
    {
        $data = [];

        foreach (SubscriptionUtils::PLANS_DETAILS as $planId => $plan) {
            $trialEndsAt = SubscriptionUtils::getProrationAnchor($planId);

            if ($trialEndsAt) {
                $trialEndsAt = $trialEndsAt->format("c");
            }

            $data[] = array_merge($plan, [
                "trial_ends_at" => $trialEndsAt,
                "trial_price" => SubscriptionUtils::getProrationPrice($planId)
            ]);
        }

        return new SuccessJsonResponse($data);
    }
}
