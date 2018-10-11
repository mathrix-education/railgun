<?php

namespace App\Controllers;

use App\Services\Braintree\Models\Plan;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

/**
 * Class PlaController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class PlanController extends Controller
{
    /**
     * Get all Braintree plans.
     *
     * @return JsonResponse
     */
    public function all()
    {
        return new JsonResponse(Plan::all());
    }

    /**
     * Fin a pla by its id.
     *
     * @param string $planId
     *
     * @return JsonResponse
     */
    public function find($planId)
    {
        return new JsonResponse(Plan::findOrFail($planId));
    }
}
