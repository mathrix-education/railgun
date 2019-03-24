<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Mark;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * GET /activities/to-revise
     * Get the activities marked as "to_revise" for the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function to_revise(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $marks = $user->marks()
            ->with("path")
            ->where("status", "=", "to_revise")
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
        $disciplinesIds = Discipline::all(["id"])
            ->pluck("id")
            ->toArray();

        /** @var Builder|null */
        $query = null;

        foreach ($disciplinesIds as $disciplineId) {
            $subQuery = $user->marks()
                ->with(["path", "path.activity.modules" => function (BelongsToMany $q) {
                    $q->limit(1);
                }])
                ->whereHas("path", function (Builder $query) use ($disciplineId) {
                    $query->where("discipline_id", "=", $disciplineId)
                        ->whereNotNull("chapter_id")
                        ->whereNotNull("notion_id")
                        ->whereNotNull("activity_id")
                        ->whereNull("module_id");
                })
                ->where("status", "=", "view")
                ->orderByDesc("updated_at")
                ->limit(1);

            if ($query === null) {
                $query = $subQuery;
            } else {
                /* @var Builder $query */
                $query->union($subQuery);
            }
        }

        $paths = $query->get();

        return new JsonResponse($paths);
    }


    /**
     * PATCH /activities/{activityId}/mark
     * Mark an activity.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $id The activity id.
     *
     * @return JsonResponse
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function mark(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Activity $activity */
        $activity = Activity::query()->findOrFail($id);
        $status = $request->input("status");

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
