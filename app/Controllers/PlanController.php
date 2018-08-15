<?php
namespace App\Controllers;

use App\Exceptions\Http404ModelNotFoundException;
use App\Services\Braintree\PlansUtils;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller;

/**
 * Class PlaController
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Controllers
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class PlanController extends Controller
{
    /**
     * Get all Braintree plans
     *
     * @return JsonResponse
     */
    public function all()
    {
        return new JsonResponse(PlansUtils::retrievePlans());
    }


    /**
     * Fin a pla by its id
     *
     * @param string $planId
     *
     * @return JsonResponse
     * @throws Http404ModelNotFoundException
     */
    public function find($planId)
    {
        $plans = PlansUtils::retrievePlans();

        foreach ($plans as $plan) {
            if ($plan["id"] === $planId) {
                return new JsonResponse($plan);
            }
        }

        throw new Http404ModelNotFoundException();
    }
}
