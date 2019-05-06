<?php

namespace App\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

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
     * GET /countries/slug/{countrySlug}
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


    /**
     * GET /countries/{countryId}/grades/{page}/{perPage}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $countryId The country id
     * @param int $page The results page.
     * @param int $perPage The number of grades per page.
     *
     * @return PaginationJsonResponse
     * @throws Http401UnauthorizedException
     */
    public function grades(Request $request, int $countryId, int $page, int $perPage): PaginationJsonResponse
    {
        return $this->relation($request, "grades", $countryId, $page, $perPage);
    }
}
