<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ActivityController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class ActivityController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /activities Index
     * @apiGroup Activities
     * @apiName ActivitiesIndex
     * @apiDescription Retrieve activities
     * @apiPermission moderator
     * @apiUse ActivityArray
     */

    /**
     * @apiVersion 0.8.1
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
     * @apiVersion 0.8.1
     * @api {post} /activities Add
     * @apiGroup Activities
     * @apiName ActivityAdd
     * @apiDescription Add a activity
     * @apiPermission moderator
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
     * @apiVersion 0.8.1
     * @api {patch} /activities/{id} Edit
     * @apiGroup Activities
     * @apiName ActivityEdit
     * @apiDescription Edit a activity
     * @apiPermission moderator
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
     * @apiVersion 0.8.1
     * @api {delete} /activities/{id} Delete
     * @apiGroup Activities
     * @apiName ActivityDelete
     * @apiDescription Delete a activity
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The activity id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
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


    /**
     * @apiVersion 0.8.1
     * @api {post} /activities/{id}/favorite Favorite
     * @apiGroup Activities
     * @apiName ActivityFavorite
     * @apiDescription Delete a activity
     * @apiUse Bearer
     * @apiPermission registered
     * @apiParam {int} id The activity id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function favorite(Request $request, int $id)
    {
        /** @var User $user */
        $user = $request->user();

        // Check authorization
        if (
            !$user->tokenCan('admin') &&
            is_int($request->input('user_id')) &&
            $user->id != $request->input('user_id')
        ) {
            return $this->http401Unauthorized();
        }

        $user = $id === null ? $request->user() : User::where('id', $id)->firstOrFail();

        $activity = Activity::where('id', $id)->firstOrFail(); // We don't need the activity itself

        $pivot = $user->activities()->where('id', $activity->id)->first();

        if ($pivot === null) {
            $user->activities()->save($activity, ['favorite' => true]);
        } else {
            $user->activities()->save($activity, [!$pivot->favorite]);
        }

        return $this->http200Success($user);
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /activities/{id}/mark Favorite
     * @apiGroup Activities
     * @apiName ActivityFavorite
     * @apiDescription Delete a activity
     * @apiUse Bearer
     * @apiPermission registered
     * @apiParam {int} id The activity id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function mark(Request $request, int $id)
    {
        $as = $request->input('status');

        if (!in_array($as, ['completed', 'to_revise', null])) {
            return $this->http400InvalidRequest('Allowed values: "completed", "to_revise", null');
        }

        /** @var User $user */
        $user = $request->user();

        // Check authorization
        if (
            !$user->tokenCan('admin') &&
            is_int($request->input('user_id')) &&
            $user->id != $request->input('user_id')
        ) {
            return $this->http401Unauthorized();
        }

        $user = $id === null ? $request->user() : User::where('id', $id)->firstOrFail();

        $activity = Activity::where('id', $id)->firstOrFail(); // We don't need the activity itself

        $user->activities()->save($activity, ['status' => $as]); // Save status

        return $this->http200Success($user);
    }
}
