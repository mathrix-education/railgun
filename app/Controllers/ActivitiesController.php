<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Mark;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * GET /activities/to-revise
     * Get the activities marked as "to_revise" for the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function toRevise(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $marks = $user->marks()
            ->with("markable")
            ->where([
                ["status", "=", "to_revise"],
                ["markable_type", "=", "activity"]
            ])
            ->get();

        return new JsonResponse($marks);
    }


    /**
     * GET /activities/view
     * Get the activities marked as "view" for the current user, one per discipline.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $marksIds = DB::table("marks")
            ->selectRaw("MAX(marks.id) as id, disciplines.id as discipline_id")
            ->join("activities", "activities.id", "=", "marks.markable_id")
            ->join("notions", "notions.id", "=", "activities.notion_id")
            ->join("chapters", "chapters.id", "=", "notions.chapter_id")
            ->join("disciplines", "disciplines.id", "=", "chapters.discipline_id")
            ->where("marks.status", "=", "view")
            ->where("marks.user_id", "=", $user->id)
            ->where("marks.markable_type", "=", "activity")
            ->groupBy("discipline_id")
            ->get()
            ->pluck("id");

        $marks = Mark::query()
            ->with(["markable"])
            ->whereIn("id", $marksIds)
            ->get();

        return new JsonResponse($marks);
    }


    /**
     * PATCH /activities/{activityId}/mark
     * Mark an activity.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $id The activity id.
     *
     * @return JsonResponse
     * @throws ValidationException
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
