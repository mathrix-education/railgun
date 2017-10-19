<?php
namespace App\Http\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Meta;
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
     */


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
     */


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
     */


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
     */


    /**
     * @apiVersion 0.8.1
     * @api {post} /activities/{id}/favorite Favorite
     * @apiGroup Activities
     * @apiName ActivityFavorite
     * @apiDescription Delete a activity
     * @apiUse Bearer
     * @apiPermission registered
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
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
            throw new Http401UnauthorizedException();
        }

        $user = $id === null ? $request->user() : User::where('id', $id)->firstOrFail();

        Activity::where('id', $id)->firstOrFail(); // We don't need the activity itself

        // Get the meta
        $meta = Meta::where([
            ['user_id', $user->id],
            ['activity_id', $id]
        ])->first();

        $metaData = array_merge(['user_id' => $user->id], $request->all());
        if ($meta === null) {
            $meta = new Meta($metaData);
        } else {
            $meta->fill($metaData);
        }

        // Check save
        $meta->save();

        return response()->json($user);
    }


    /**
     * @apiVersion 0.8.1
     * @api {get} /activities/favorite Favorites
     * @apiGroup Activities
     * @apiName ActivitiesFavorite
     * @apiDescription Get user favorite activities
     * @apiUse Bearer
     * @apiPermission registered
     * @apiUse MetaArray
     * @apiUse 401Unauthorized
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function favorites(Request $request)
    {
        $metas = Meta::with(['country', 'class_', 'discipline', 'notion', 'activity'])
            ->where([
                ['user_id' => $request->user()->id],
                ['favorite' => true]
            ])
            ->get();
        return response()->json($metas);
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /activities/{id}/mark Mark
     * @apiGroup Activities
     * @apiName ActivityMark
     * @apiDescription Mark activity as completed or to_revise
     * @apiUse Bearer
     * @apiPermission registered
     * @apiUse MetaData
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Http400InvalidRequestException
     */
    public function mark(Request $request, int $id)
    {
        $user = $this->getUserByRequestOrUnauthorized($request);

        $metaData = array_merge(
            ['user_id' => $user->id, 'activity_id' => $id],
            $request->only(['country_id', 'class_id', 'discipline_id', 'notion_id'])
        );

        /** @var Meta $meta */
        $meta = Meta::firstOrNew($metaData);
        $meta->status = $request->input('status');
        $meta->save();

        return response()->json($user);
    }
}
