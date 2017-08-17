<?php

namespace App\Http\Controllers;

use App\Models\Class_;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Http\Controllers\BaseController;
use Zend\Diactoros\Request;

/**
 * Class ClassController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ClassController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /classes Index
     * @apiGroup Classes
     * @apiName ClassesIndex
     * @apiDescription Retrieve classes
     * @apiSuccess {array} - Array of classes
     * @apiUse Class
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Class_::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /classes/{country_short_slug} byCountry
     * @apiGroup Classes
     * @apiName ClassesByCountry
     * @apiDescription Retrieve classes based on country
     * @apiSuccess {array} - Array of classes
     * @apiUse Class
     * @apiError (Error 404) model_not_found Country with given `slug_short` does not exist
     *
     * @param Country $country
     *
     * @return JsonResponse
     */
    public function byCountry(Country $country): JsonResponse
    {
        return $this->http200Success($country->classes);
    }

    /**
     * @param int $classId
     */
    public function get(int $classId)
    {
        die('lol');
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /classes Add
     * @apiGroup Classes
     * @apiName ClassAdd
     * @apiDescription Retrieve classes
     * @apiParam (Data) {string} name The class name
     * @apiParam (Data) {string} [name_short=name] The class short name
     * @apiParam (Data) {string} [slug=slug(name)] The class slug
     * @apiParam (Data) {string} [slug=slug(name_short)] The class short slug
     * @apiUse Class
     * @apiError (Error 401) unauthorized User does not have enough privileges
     * @apiError (Error 422) validation Class data failed to pass validation
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        return $this->http501NotImplemented();
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /classes/{id} Edit
     * @apiGroup Classes
     * @apiName ClassEdit
     * @apiDescription Edit a class
     * @apiParam {int} id The class id
     * @apiParam (Data) {string} name The class name
     * @apiParam (Data) {string} [name_short=name] The class short name
     * @apiParam (Data) {string} [slug=slug(name)] The class slug
     * @apiParam (Data) {string} [slug=slug(name_short)] The class short slug
     * @apiUse Class
     * @apiError (Error 401) unauthorized User does not have enough privileges
     * @apiError (Error 404) model_not_found Class with given id does not exist
     * @apiError (Error 422) validation Class data failed to pass validation
     *
     * @param Request $request
     * @param Class_ $class
     *
     * @return JsonResponse
     */
    public function edit(Request $request, Class_ $class)
    {
        return $this->http501NotImplemented();

    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /classes/{id} Delete
     * @apiGroup Classes
     * @apiName ClassDelete
     * @apiDescription Retrieve classes
     * @apiPermission admin
     * @apiParam {int} id The class id
     * @apiError (Error 401) unauthorized User does not have enough privileges
     * @apiError (Error 404) model_not_found Class with given id does not exist
     *
     * @param Class_ $class
     *
     * @return JsonResponse
     */
    public function delete(Class_ $class)
    {
        return $this->http501NotImplemented();
    }
}
