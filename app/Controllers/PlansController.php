<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Stripe\SubscriptionUtils;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\DataResponse;
use function array_merge;
use function count;

/**
 * Serve routes starting with /plans.
 */
class PlansController extends BaseController
{
    /**
     * GET /plans
     * Get the available plans.
     *
     * @param Request $request
     *
     * @return DataResponse
     */
    public function all(Request $request): DataResponse
    {
        $data = [];

        foreach (SubscriptionUtils::PLANS_DETAILS as $planId => $plan) {
            $trialEndsAt = SubscriptionUtils::getProrationAnchor($planId);

            if ($trialEndsAt) {
                $trialEndsAt = $trialEndsAt->format('c');
            }

            $data[] = array_merge(
                $plan,
                [
                    'proration_ends_at' => $trialEndsAt,
                    'proration_price'   => SubscriptionUtils::getProrationPrice($planId),
                ]
            );
        }

        return new DataResponse($data, [
            'page'     => $request->query('page', 0),
            'per_page' => $request->query('per_page', 1),
            'total'    => count($data),
        ]);
    }
}
