<?php
namespace App\Controllers;

use App\Services\Braintree\Formatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
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
        return new JsonResponse(
            Cache::remember("braintree-plans", 60, function () {
                return Formatter::cast(\Braintree_Plan::all());
            })
        );
    }
}
