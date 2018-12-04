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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
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
            $activity->setAttribute("seo_index", $seoIndex);

            $activities->offsetSet($key, $activities);
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
            ->with("mark.path.activity")
            ->where("status", "=", "to_revise")
            ->get();

        $activities = $marks->map(function (Mark $mark) {
            return $mark->path->activity;
        });

        $response = new JsonResponse($activities);

        return $response;
    }

    /**
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function incomplete(Request $request): JsonResponse
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
            $subQuery = $user->marks()->whereHas("paths", function (HasMany $query) use ($disciplineId) {
                $query->where("discipline_id", "=", $disciplineId);
            })
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

        $path = Path::retrieve(
            array_merge(
                ["activity_id" => $id],
                $request->only(["country_id", "class_id", "discipline_id", "notion_id", "chapter_id"])
            )
        );

        $mark = Mark::retrieve([
            "user_id" => $user->id,
            "path_id" => $path->id
        ]);
        $mark->status = $request->input("status");
        $mark->save();

        $activity = Activity::findOrFail($id);
        $response = new JsonResponse($activity);

        return $response;
    }
}
