<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http501NotImplementedException;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CountryController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix
 */
class CountryController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /countries/{?page}/{?perPage} Index
     * @apiGroup Countries
     * @apiName CountriesIndex
     * @apiDescription Retrieve countries
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse CountryArray
     */


    /**
     * @apiVersion 1.0.0
     * @api {get} /countries/{id} Get
     * @apiGroup Countries
     * @apiName CountryGet
     * @apiDescription Get a country by id
     * @apiPermission public
     * @apiParam {int} id The country id
     * @apiUse Country
     * @apiUse Http404ModelNotFound
     */


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
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


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
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 1.0.0
     * @api {delete} /countries/{id} Delete (Not implemented)
     * @apiGroup Countries
     * @apiName CountryDelete
     * @apiDescription Delete a country
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The country id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http501NotImplementedException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws \Exception
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $country = Country::find($id);
        $request->user()->canOrFail("delete", $country);

        throw new Http501NotImplementedException();

        return parent::delete($request, $id);
    }
}
