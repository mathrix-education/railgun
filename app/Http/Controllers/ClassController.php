<?php
namespace App\Http\Controllers;

use App\Exceptions\Http501NotImplementedException;
use App\Models\Class_;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClassController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class ClassController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /classes Index
     * @apiGroup Classes
     * @apiName ClassesIndex
     * @apiDescription Retrieve classes
     * @apiPermission public
     * @apiUse ClassArray
     * @apiUse Http400InvalidRequest_Pagination
     */

    /**
     * @apiVersion 0.8.1
     * @api {get} /classes/{id} Get
     * @apiGroup Classes
     * @apiName ClassGet
     * @apiDescription Get a class by id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiUse Class
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 0.8.1
     * @api {post} /classes Add
     * @apiGroup Classes
     * @apiName ClassAdd
     * @apiDescription Add class
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse ClassData
     * @apiUse Class
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 0.8.1
     * @api {patch} /classes/{id} Edit
     * @apiGroup Classes
     * @apiName ClassEdit
     * @apiDescription Edit a class
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The class id
     * @apiUse ClassData
     * @apiUse Class
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 0.8.1
     * @api {delete} /classes/{id} Delete (Not implemented)
     * @apiGroup Classes
     * @apiName ClassDelete
     * @apiDescription Delete a class
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The class id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http501NotImplementedException
     */
    public function delete(Request $request, int $id)
    {
        throw new Http501NotImplementedException();
    }
}
