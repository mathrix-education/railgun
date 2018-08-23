<?php

namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http501NotImplementedException;
use App\Models\Class_;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClassController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ClassController extends BaseController
{
    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /classes/{?page}/{?perPage} Index
     * @apiGroup       Classes
     * @apiName        ClassesIndex
     * @apiDescription Retrieve classes
     * @apiPermission  public
     * @apiUse         Pagination
     * @apiUse         ClassArray
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /classes/{id} Get
     * @apiGroup       Classes
     * @apiName        ClassGet
     * @apiDescription Get a class by id
     * @apiPermission  public
     * @apiParam {int} id The class id
     * @apiUse         ClassModel
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /classes Add
     * @apiGroup       Classes
     * @apiName        ClassAdd
     * @apiDescription Add class
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiUse         ClassRequest
     * @apiUse         ClassModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelValidation
     * @apiUse         Http422ModelSave
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {patch} /classes/{id} Edit
     * @apiGroup       Classes
     * @apiName        ClassEdit
     * @apiDescription Edit a class
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiParam {int} id The class id
     * @apiUse         ClassRequest
     * @apiUse         ClassModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     * @apiUse         Http422ModelSave
     * @apiUse         Http422ModelValidation
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {delete} /classes/{id} Delete (Not implemented)
     * @apiGroup       Classes
     * @apiName        ClassDelete
     * @apiDescription Delete a class
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiParam {int} id The class id
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http501NotImplementedException
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $class = Class_::find($id);
        $request->user()->canOrFail("delete", $class);

        throw new Http501NotImplementedException();
        return parent::delete($request, $id);
    }
}
