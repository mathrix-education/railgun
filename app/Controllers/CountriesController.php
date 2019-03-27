<?php

namespace App\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class CountriesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class CountriesController extends BaseController
{
    /**
     * GET /countries/slug{countrySlug}
     *
     * @param string $countrySlug The country slug.
     *
     * @return JsonResponse
     */
    public function getBySlug(string $countrySlug)
    {
        $country = Country::query()
            ->where("slug", "=", $countrySlug)
            ->firstOrFail();

        return new JsonResponse($country);
    }
}
