<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class ActivityController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.9a-1
 * @copyright Mathrix
 */
class ActivityController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /activities Index
     * @apiGroup Activities
     * @apiName ActivitiesIndex
     * @apiDescription Retrieve activities
     * @apiPermission public
     * @apiUse ActivityArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Activity::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /activities/{id} Get
     * @apiGroup Activities
     * @apiName ActivityGet
     * @apiDescription Get a activity by id
     * @apiPermission public
     * @apiParam {int} id The activity id
     * @apiUse Activity
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $activity = Activity::where('id', $id)->firstOrFail();

        return $this->http200Success($activity);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /activities Add
     * @apiGroup Activities
     * @apiName ActivityAdd
     * @apiDescription Add a activity
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse ActivityData
     * @apiUse Activity
     * @apiUse 401Unauthorized
     * @apiUse 422Validation
     * @apiUse 422Save
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        if ($request->user()->cant('add', Activity::class)) {
            return $this->http401Unauthorized();
        }

        $activity = new Activity();
        $activity->fill($request->all());

        if (!$activity->save()) {
            return $activity->handleResponse($this->http422Save());
        }

        return $this->http200Success($activity);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /activities/{id} Edit
     * @apiGroup Activities
     * @apiName ActivityEdit
     * @apiDescription Edit a activity
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The activity id
     * @apiUse ActivityData
     * @apiUse Activity
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     * @apiUse 422Save
     * @apiUse 422Validation
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id)
    {
        /** @var Activity $activity */
        $activity = Activity::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', Activity::class)) {
            return $this->http401Unauthorized();
        }

        $activity->fill($request->all());

        if (!$activity->save()) {
            return $activity->handleResponse($this->http422Save());
        }

        return $this->http200Success($activity);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /activities/{id} Delete
     * @apiGroup Activities
     * @apiName ActivityDelete
     * @apiDescription Delete a activity
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The activity id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        /** @var Activity $activity */
        $activity = Activity::where('id', '=', $id)->firstOrFail();

        if ($request->user()->cant('delete', $activity)) {
            return $this->http401Unauthorized();
        }

        if (!$activity->delete()) {
            return $activity->handleResponse($this->http422Delete());
        }

        return $this->http200Success();
    }
}
