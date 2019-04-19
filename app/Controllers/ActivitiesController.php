<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Mark;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class ActivitiesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ActivitiesController extends BaseController
{
    /**
     * PATCH /activities/{activityId}/mark
     * Mark an activity.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $id The activity id.
     *
     * @return JsonResponse
     */
    public function mark(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Activity $activity */
        $activity = Activity::query()->findOrFail($id);
        $status = $request->json("status");

        $mark = Mark::retrieve($activity, $user);
        $mark->status = $status;
        $mark->save();

        $cacheKey = "user.{$user->id}.chapter.{$activity->notion->chapter->id}.recursive";
        DB::table("cache")->where("key", "=", $cacheKey)->delete();

        return new JsonResponse($activity->fresh());
    }


    /**
     * PATCH /activities/{activityId}/unmark
     * Unmark an activity.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $id The activity id.
     *
     * @return JsonResponse
     */
    public function unmark(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Activity $activity */
        $activity = Activity::query()->findOrFail($id);

        Mark::retrieveAll($activity, $user)
            ->each(function (Mark $mark) {
                $mark->delete();
            });

        $cacheKey = "user.{$user->id}.chapter.{$activity->notion->chapter->id}.recursive";
        DB::table("cache")->where("key", "=", $cacheKey)->delete();

        return new JsonResponse($activity->fresh());
    }
}
