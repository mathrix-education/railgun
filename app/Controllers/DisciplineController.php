<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http501NotImplementedException;

use App\Models\Discipline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DisciplineController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix
 */
class DisciplineController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /disciplines/{?page}/{?perPage} Index
     * @apiGroup Disciplines
     * @apiName DisciplinesIndex
     * @apiDescription Retrieve disciplines
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse DisciplineArray
     */


    /**
     * @apiVersion 1.0.0
     * @api {get} /disciplines/{id} Get
     * @apiGroup Disciplines
     * @apiName DisciplineGet
     * @apiDescription Get a discipline by id
     * @apiPermission public
     * @apiParam {int} id The discipline id
     * @apiUse Discipline
     * @apiUse Http404ModelNotFound
     */

    /**
     * @apiVersion 1.0.0
     * @api {post} /disciplines Add
     * @apiGroup Disciplines
     * @apiName DisciplineAdd
     * @apiDescription Add a discipline
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse DisciplineData
     * @apiUse Discipline
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 1.0.0
     * @api {patch} /disciplines/{id} Edit
     * @apiGroup Disciplines
     * @apiName DisciplineEdit
     * @apiDescription Edit a discipline
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The discipline id
     * @apiUse DisciplineData
     * @apiUse Discipline
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 1.0.0
     * @api {delete} /disciplines/{id} Delete (Not implemented)
     * @apiGroup Disciplines
     * @apiName DisciplineDelete
     * @apiDescription Delete a discipline
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The discipline id
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
        $discipline = Discipline::find($id);
        $request->user()->canOrFail("delete", $discipline);

        throw new Http501NotImplementedException();

        return parent::delete($request, $id);
    }
}
