<?php

namespace App\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;
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
     * @param int $countryId The country id
     * @param int $page The results page.
     * @param int $perPage The number of grades per page.
     *
     * @return PaginationJsonResponse
     */
    public function grades(int $countryId, int $page, int $perPage): PaginationJsonResponse
    {
        /** @var Country $country */
        $country = Country::query()->findOrFail($countryId);

        return new PaginationJsonResponse($country->grades(), $page, $perPage);
    }
}
