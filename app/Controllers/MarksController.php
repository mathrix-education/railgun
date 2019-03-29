<?php

namespace App\Controllers;

use App\Models\Mark;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class MarksController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class MarksController extends BaseController
{
    /**
     * GET /marks/activities/to-revise
     * Get the activities marked as "to_revise" for the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function activitiesToRevise(Request $request): JsonResponse
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
     * GET /marks/activities/view
     * Get the activities marked as "view" for the current user, one per discipline.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function activitiesView(Request $request): JsonResponse
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
}
