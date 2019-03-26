<?php

namespace App\Controllers\Braintree;

use App\Services\Braintree\Models\Plan;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class PlansController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class PlansController extends BaseController
{
    /**
     * GET /plans
     * Get all Braintree plans.
     *
     * @return JsonResponse
     */
    public function all()
    {
        return new JsonResponse(Plan::all()->toArray());
    }


    /**
     * GET /plans/{planId}
     * Find a plan by its id.
     *
     * @param string $planId The play id.
     *
     * @return JsonResponse
     */
    public function find(string $planId)
    {
        return new JsonResponse(Plan::findOrFail($planId));
    }


    /**
     * GET /{planId}/estimate
     * Estimate the price of the trial before the regular subscription billing cycles.
     *
     * @param string $planId
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     */
    public function estimate(string $planId)
    {
        if (!in_array($planId, ["v4_annually", "v4_biannually"])) {
            throw new Http400BadRequestException([
                "planId" => $planId
            ], "The plan {$planId} cannot be estimated.");
        }

        $plan = Plan::findOrFail($planId);
        $data = [
            "planId" => $plan->id,
            "trialPrice" => $plan->trialPrice,
            "trialEndDate" => $plan->trialEndDate->__toString() // Hack?
        ];

        return new JsonResponse($data);
    }
}
