<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Mark;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\ValidationException;

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

        return new JsonResponse($activity->refresh());
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

        return new JsonResponse($activity->refresh());
    }
}
