<?php
namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class ClassController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ClassController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /classes/{country_short_slug}
     * @apiGroup Classes
     * @apiName ClassesIndex
     * @apiDescription Retrieve classes based on country
     * @apiUse Class
     *
     * @param Country $country
     *
     * @return JsonResponse
     */
    public function index(Country $country): JsonResponse
    {
        return response()->json($country->classes);
    }
}
