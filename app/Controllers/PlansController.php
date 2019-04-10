<?php

namespace App\Controllers;

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
    public function all(): JsonResponse
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
    public function find(string $planId): JsonResponse
    {
        return new JsonResponse(Plan::findOrFail($planId));
    }
}
