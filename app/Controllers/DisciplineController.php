<?php
namespace App\Controllers;

use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http501NotImplementedException;
use App\Models\Class_;
use App\Models\Discipline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DisciplineController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class DisciplineController extends BaseController
{
    /**
     * Get visible disciplines by class id
     *
     * @param Request $request
     * @param int     $classId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function byClass(Request $request, int $classId)
    {
        $class = Class_::findOrFail($classId);

        if ($request->user() !== null && $request->user()
                ->cant("byClass", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        $response = new JsonResponse($class->disciplines);

        return $response;
    }
    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /disciplines/{?page}/{?perPage} Index
     * @apiGroup       Disciplines
     * @apiName        DisciplinesIndex
     * @apiDescription Retrieve disciplines
     * @apiPermission  public
     * @apiUse         Pagination
     * @apiUse         DisciplineArray
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /disciplines/{id} Get
     * @apiGroup       Disciplines
     * @apiName        DisciplineGet
     * @apiDescription Get a discipline by id
     * @apiPermission  public
     * @apiParam {int} id The discipline id
     * @apiUse         DisciplineModel
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /disciplines Add
     * @apiGroup       Disciplines
     * @apiName        DisciplineAdd
     * @apiDescription Add a discipline
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiUse         DisciplineRequest
     * @apiUse         DisciplineModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelValidation
     * @apiUse         Http422ModelSave
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {delete} /disciplines/{id} Delete (Not implemented)
     * @apiGroup       Disciplines
     * @apiName        DisciplineDelete
     * @apiDescription Delete a discipline
     * @apiUse         Bearer
     * @apiPermission  admin
     * @apiParam {int} id The discipline id
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     *
     * @param Request $request The request
     * @param int     $id
     *
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http501NotImplementedException
     *
     * @return JsonResponse
     */
}
