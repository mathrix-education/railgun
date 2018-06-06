<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http404ModelNotFoundException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Discount;
use App\Models\Log;
use App\Models\Purchase;
use App\Models\Training;
use App\Models\User;
use App\Utils\StripeUtils;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TrainingController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
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
     * @apiPermission public
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
     * @apiPermission admin
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
     * @apiPermission admin
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
     * @apiPermission admin
     * @apiParam {int} id The training id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */

    /**
     * @apiVersion 4.0.1
     * @api {get} /trainings/{id}/recursive Recursive
     * @apiGroup Trainings
     * @apiName TrainingRecursive
     * @apiDescription Recursively explore a training
     * @apiUse Bearer
     * @apiPermission registered if paid, admin otherwise
     * @apiParam {int} id The training id
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http404ModelNotFoundException
     */
    public function recursive(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $canSeeInvisible = $user !== null && $user->isModerator();

        /** @var Training $training */
        $training = Training::with([
            "sections" => function (HasMany $query) use ($canSeeInvisible) {
                $query->orderBy("order");

                if (!$canSeeInvisible) {
                    $query->where("visible", true);
                }
            },
            "sections.lessons" => function (HasMany $query) use ($canSeeInvisible) {
                $query->orderBy("order");

                if (!$canSeeInvisible) {
                    $query->where("visible", true);
                }
            },
            "sections.lessons.module"
        ])
            ->whereId($id)
            ->firstOrFail();

        if (!$training->visible && !$canSeeInvisible) {
            throw new Http404ModelNotFoundException();
        }

        $user->canOrFail("recursive", $training);

        return new JsonResponse($training);
    }
}
