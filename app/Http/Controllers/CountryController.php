<?php
namespace App\Http\Controllers;

use App\Exceptions\Http501NotImplementedException;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CountryController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class CountryController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /countries/{?page}/{?perPage} Index
     * @apiGroup Countries
     * @apiName CountriesIndex
     * @apiDescription Retrieve countries
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse CountryArray
     */


    /**
     * @apiVersion 0.8.1
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
     * @apiVersion 0.8.1
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
     * @apiVersion 0.8.1
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
     * @apiVersion 0.8.1
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
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        throw new Http501NotImplementedException();

        return parent::delete($request, $id);
    }
}
