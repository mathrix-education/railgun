<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Meta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ActivityController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class ActivityController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /activities/{?page}/{?perPage} Index
     * @apiGroup Activities
     * @apiName ActivitiesIndex
     * @apiDescription Retrieve activities
     * @apiPermission moderator
     * @apiUse Pagination
     * @apiUse ActivityArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 1.0.0
     * @api {get} /activities/{id} Get
     * @apiGroup Activities
     * @apiName ActivityGet
     * @apiDescription Get an activity by id
     * @apiPermission public
     * @apiParam {int} id The activity id
     * @apiUse Activity
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 1.0.0
     * @api {post} /activities Add
     * @apiGroup Activities
     * @apiName ActivityAdd
     * @apiDescription Add a activity
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse ActivityData
     * @apiUse Activity
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 1.0.0
     * @api {patch} /activities/{id} Edit
     * @apiGroup Activities
     * @apiName ActivityEdit
     * @apiDescription Edit an activity
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The activity id
     * @apiUse ActivityData
     * @apiUse Activity
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 1.0.0
     * @api {delete} /activities/{id} Delete
     * @apiGroup Activities
     * @apiName ActivityDelete
     * @apiDescription Delete an activity
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The activity id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 1.0.0
     * @api {post} /activities/search Search
     * @apiGroup Activities
     * @apiName ActivitiesSearch
     * @apiDescription Search in activities
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse ActivityArray
     * @apiUse Http400InvalidRequest
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 1.0.0
     * @api {get} /activities/favorites Favorites
     * @apiGroup Activities
     * @apiName ActivitiesFavorite
     * @apiDescription Get user favorite activities
     * @apiUse Bearer
     * @apiPermission registered
     * @apiUse MetaArray
     * @apiUse Http401Unauthorized
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     */
    public function favorites(Request $request): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);
        $metas = Meta::with(["country", "class_", "discipline", "chapter", "notion", "activity", "activity.modules"])
            ->where([
                ["user_id", "=", $user->id],
                ["favorite", "=", true]
            ])
            ->get();

        return response()->json($metas);
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /activities/to-revise To revise
     * @apiGroup Activities
     * @apiName ActivitiesToRevise
     * @apiDescription Get user to_revise activities
     * @apiUse Bearer
     * @apiPermission registered
     * @apiUse MetaArray
     * @apiUse Http401Unauthorized
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     */
    public function to_revise(Request $request): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);
        $metas = Meta::with(["country", "class_", "discipline", "chapter", "notion", "activity", "activity.modules"])
            ->where([
                ["user_id", "=", $user->id],
                ["status", "=", "to_revise"]
            ])
            ->get();

        return response()->json($metas);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /activities/{id}/mark Mark
     * @apiGroup Activities
     * @apiName ActivityMark
     * @apiDescription Mark an activity as favorite or set its status completed or to_revise
     * @apiUse Bearer
     * @apiPermission registered
     * @apiUse MetaData
     * @apiUse Http400InvalidRequest
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function mark(Request $request, int $id): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);

        $metaData = array_merge(
            ["user_id" => $user->id, "activity_id" => $id],
            $request->only(["country_id", "class_id", "discipline_id", "notion_id", "chapter_id"])
        );

        /** @var Meta $meta */
        $meta = Meta::firstOrNew($metaData);

        // Set favorite
        $favorite = $request->input("favorite");
        if ($favorite === null) {
            if (!$meta->exists()) {
                $meta->favorite = false;
            }
        } else {
            $meta->favorite = $favorite;
        }

        // Set status
        $status = $request->input("status");
        if ($status === null) {
            if (!$meta->exists()) {
                $meta->status = null;
            }
        } else {
            $meta->status = $status;
        }

        $meta->save();

        return response()->json(Activity::find($id));
    }
}
