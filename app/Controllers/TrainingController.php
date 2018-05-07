<?php
namespace App\Controllers;

use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TrainingController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class TrainingController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /trainings/{?page}/{?perPage} Index
     * @apiGroup Trainings
     * @apiName TrainingsIndex
     * @apiDescription Retrieve trainings
     * @apiPermission moderator
     * @apiUse Pagination
     * @apiUse TrainingArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /trainings/{id} Get
     * @apiGroup Trainings
     * @apiName TrainingGet
     * @apiDescription Get an training by id
     * @apiPermission public
     * @apiParam {int} id The training id
     * @apiUse TrainingModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {post} /trainings Add
     * @apiGroup Trainings
     * @apiName TrainingAdd
     * @apiDescription Add a training
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse TrainingRequest
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 4.0.0
     * @api {patch} /trainings/{id} Edit
     * @apiGroup Trainings
     * @apiName TrainingEdit
     * @apiDescription Edit an training
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The training id
     * @apiUse TrainingRequest
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 4.0.0
     * @api {delete} /trainings/{id} Delete
     * @apiGroup Trainings
     * @apiName TrainingDelete
     * @apiDescription Delete an training
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The training id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */

    /**
     * @apiVersion 4.0.1
     * @api {get} /training/{id}/recursive Recursive
     * @apiGroup Trainings
     * @apiName TrainingRecursive
     * @æpiDescription Recursively explore a training
     * @apiUse Bearer
     * @apiPermission registered if paid
     * @apiParam {int} id The training id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function recursive(Request $request, int $id): JsonResponse
    {
        $training = Training::with([
            "sections",
            "sections.lessons",
            "sections.lessons.module"
        ])
            ->whereId($id)
            ->firstOrFail();

        $request->user()->canOrFail("get", $training);

        return new JsonResponse($training);
    }
}
