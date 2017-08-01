<?php
namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;

/**
 * Class ClassController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ClassController extends BaseController
{
    /**
     * Retrieve classes based on country
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
