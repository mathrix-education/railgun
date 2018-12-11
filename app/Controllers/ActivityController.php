<?php

namespace App\Controllers;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Discipline;
use App\Models\Mark;
use App\Models\Notion;
use App\Models\Path;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ActivityController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ActivityController extends BaseController
{
    /**
     * Get the activities associated with a given route with the seo_index boolean.
     *
     * @param int $countryId
     * @param int $classId
     * @param int $disciplineId
     * @param int $chapterId
     * @param int $notionId
     *
     * @return JsonResponse
     */
    public function byRoute(int $countryId, int $classId, int $disciplineId, int $chapterId, int $notionId)
    {
        $activities = Notion::findOrFail($notionId)->activities;

        /**
         * @var int      $key
         * @var Activity $activity
         */
        foreach ($activities as $key => $activity) {
            $seoIndex = Path::isIndexed($countryId, $classId, $disciplineId, $chapterId, $notionId, $activity->id);
            $activity->seo_index = $seoIndex;

            $activities->offsetSet($key, $activity);
        }

        $response = new JsonResponse($activities);

        return $response;
    }

    /**
     * @param Request $request The request
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

        $response = new JsonResponse($marks);

        return $response;
    }

    /**
     * @param Request $request The request
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

        /**
         * @var Builder|null
         */
        $query = null;

        foreach ($disciplinesIds as $disciplineId) {
            $subQuery = $user->marks()
                ->with("path")
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
        $response = new JsonResponse($paths);

        return $response;
    }

    /**
     * @param Request $request The request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function mark(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        Activity::findOrFail($id);
        $status = $request->input("status");

        $path = Path::retrieve(
            array_merge(
                $request->json("path"),
                ["activity_id" => $id]
            )
        );

        $mark = Mark::retrieve([
            "user_id" => $user->id,
            "path_id" => $path->id
        ]);

        $mark->status = $status;
        $mark->save();

        $activity = Activity::findOrFail($id);
        $response = new JsonResponse($activity);

        return $response;
    }

    /**
     * Unmark and activity.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function unmark(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Activity $activity */
        Activity::findOrFail($id);

        $path = Path::retrieve(
            array_merge(
                $request->json("path"),
                ["activity_id" => $id]
            )
        );

        $marks = Mark::where([
            ["user_id", "=", $user->id],
            ["path_id", "=", $path->id]
        ])
            ->get();

        // Delete marks
        $marks->each(function (Mark $mark) {
            $mark->delete();
        });

        /** @var Activity $activity */
        $activity = Activity::findOrFail($id);
        $response = new JsonResponse($activity);

        return $response;
    }
}
