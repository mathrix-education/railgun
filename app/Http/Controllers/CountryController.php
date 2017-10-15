<?php
namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CountryController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.9a-1
 * @copyright Mathrix
 */
class CountryController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /countries Index
     * @apiGroup Countries
     * @apiName CountriesIndex
     * @apiDescription Retrieve countries
     * @apiPermission public
     * @apiUse CountryArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Country::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /countries/{id} Get
     * @apiGroup Countries
     * @apiName CountryGet
     * @apiDescription Get a country by id
     * @apiPermission public
     * @apiParam {int} id The country id
     * @apiUse Country
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $country = Country::where('id', $id)->firstOrFail();
        return $this->http200Success($country);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /countries Add
     * @apiGroup Countries
     * @apiName CountryAdd
     * @apiDescription Add a country
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse CountryData
     * @apiUse Country
     * @apiUse 401Unauthorized
     * @apiUse 422Validation
     * @apiUse 422Save
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        if ($request->user()->cant('add', Country::class)) {
            return $this->http401Unauthorized();
        }

        $country = new Country();
        $country->fill($request->all());

        if (!$country->save()) {
            return $country->handleResponse($this->http422Save());
        }

        return $this->http200Success($country);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /countries/{id} Edit
     * @apiGroup Countries
     * @apiName CountryEdit
     * @apiDescription Edit a country
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The country id
     * @apiUse CountryData
     * @apiUse Country
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     * @apiUse 422Save
     * @apiUse 422Validation
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id)
    {
        /** @var Country $country */
        $country = Country::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', $country)) {
            return $this->http401Unauthorized();
        }

        $country->fill($request->all());

        if (!$country->save()) {
            return $country->handleResponse($this->http422Save());
        }

        return $this->http200Success($country);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /countries/{id} Delete (Not implemented)
     * @apiGroup Countries
     * @apiName CountryDelete
     * @apiDescription Delete a country
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The country id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(int $id)
    {
        return $this->http501NotImplemented();
    }
}
