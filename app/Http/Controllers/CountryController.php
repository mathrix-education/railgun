<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

class CountryController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /countries Index
     * @apiGroup Countries
     * @apiName CountriesIndex
     * @apiSuccess {array} - Array of countries
     * @apiUse Country
     */
    public function index()
    {
        return $this->http200Success(Country::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /countries Get
     * @apiGroup Countries
     * @apiName CountryGet
     * @apiUse Country
     * @apiError (Error 404) model_not_found The country with given id was not found
     * @param Country $country
     * @return JsonResponse
     */
    public function get(Country $country)
    {
        return $this->http200Success($country);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /countries/ Add
     * @apiGroup Countries
     * @apiName CountryAdd
     * @apiParam {int} id The country id
     * @apiParam (Data) {string} name The country name
     * @apiParam (Data) {string} [name_short=name] The country short name
     * @apiParam (Data) {string} [slug=slug(name)] The country slug
     * @apiParam (Data) {string} [slug=slug(name_short)] The country short slug
     * @apiUse Country
     * @apiError (Error 401) unauthorized User odes not have enough privileges
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        return $this->http501NotImplemented();
    }

    /**
     * @apiVersion 1.0.0
     * @api {patch} /countries/{id} Edit
     * @apiGroup Countries
     * @apiName CountryEdit
     * @apiParam {int} id The country id
     * @apiParam (Data) {string} name The country name
     * @apiParam (Data) {string} [name_short=name] The country short name
     * @apiParam (Data) {string} [slug=slug(name)] The country slug
     * @apiParam (Data) {string} [slug=slug(name_short)] The country short slug
     * @apiUse Country
     * @apiError (Error 401) unauthorized User does not have enough privileges
     * @apiError (Error 404) model_not_found Country with given id was not found
     * @apiError (Error 422) validation Country data failed to pass validation
     * @param Country $country
     * @return JsonResponse
     */
    public function edit(Country $country)
    {
        return $this->http501NotImplemented();
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /countries/{id} Delete
     * @apiGroup Countries
     * @apiName CountryDelete
     * @apiParam {int} id The country id
     * @apiError (Error 401) unauthorized User does not have enough privileges
     * @apiError (Error 404) model_not_found Country with given id was not found
     * @apiError (Error 422) validation Country data failed to pass validation
     * @param Country $country
     * @return JsonResponse
     */
    public function delete(Country $country)
    {
        return $this->http501NotImplemented();
    }
}