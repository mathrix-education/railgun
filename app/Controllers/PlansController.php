<?php

namespace App\Controllers;

use App\Services\Stripe\SubscriptionUtils;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

class PlansController extends BaseController
{
    public function all()
    {
        $data = [];

        foreach (SubscriptionUtils::PLANS_DETAILS as $planId => $plan) {
            $data[] = array_merge($plan, [
                "trial_days" => SubscriptionUtils::getTrialDuration($planId),
                "trial_ends_at" => SubscriptionUtils::getTrialEndDate($planId),
                "trial_price" => SubscriptionUtils::getTrialPeriodPrice($planId)
            ]);
        }

        return new SuccessJsonResponse($data);
    }
}
