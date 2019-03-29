<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Notion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class SearchController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SearchController extends BaseController
{
    /**
     * GET /search/{query}
     * Search in content with a single query.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $query The search query.
     * @param int $page The page.
     * @param int $perPage The number of results (videos, chapters, notions) per page.
     *
     * @return JsonResponse
     */
    public function content(Request $request, string $query, int $page = 0, $perPage = 100)
    {
        $query = trim(mb_strtolower(urldecode($query))); // Trim and decode query
        $query = implode("%%", explode(" ", $query));

        /** @var User $user */
        $user = $request->user();

        $chapters = new Collection();
        $notions = new Collection();
        $gradeIds = [];

        // Videos
        $videos = Activity::query()
            ->where("visible", "=", true)
            ->where("name", "LIKE", "%$query%")
            ->orWhere("slug", "LIKE", "%$query%")
            ->whereHas("modules", function (Builder $q) use ($query) {
                return $q->where("type", "course")
                    ->where("content", "LIKE", "%[video]%");
            })
            ->with([
                "notion",
                "notion.chapter",
                "notion.chapter.discipline",
                "notion.chapter.grade",
                "notion.chapter.grade.country",
                "modules" => function (HasMany $q) use ($query) {
                    return $q->where("type", "course")
                        ->where("content", "LIKE", "%[video]%");
                }
            ])
            ->limit($perPage)
            ->offset($page * $perPage)
            ->get();

        // Custom requests for logged users
        if ($user && $user->grade_id !== null) {
            /** @var Grade $userClass */
            $userClass = Grade::query()->findOrFail($user->grade_id);
            $cursus = $userClass->cursus();
            $gradeIds = $cursus->isNotEmpty() ? $cursus->pluck("id")
                ->toArray() : [];

            $chapters = Chapter::query()
                ->whereIn("grade_id", $gradeIds)
                ->with(["discipline", "grade", "grade.country"])
                ->where("name", "LIKE", "%$query%")
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage)
                ->orderByDesc("grade_id")
                ->offset($page * $perPage)
                ->get();

            $notions = Notion::query()
                ->where("name", "LIKE", "%$query%")
                ->whereHas("chapter.grade", function (Builder $query) use ($gradeIds) {
                    return $query->whereIn("id", $gradeIds);
                })
                ->with([
                    "chapter.discipline",
                    "chapter.grade" => function (BelongsTo $query) use ($gradeIds) {
                        return $query->orderByDesc("id");
                    },
                    "chapter.grade.country"
                ])
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get();
        }

        // Not logged user or not filled collection
        if ($chapters->count() < $perPage) {
            $chaptersToAdd = Chapter::with(["discipline", "grade", "grade.country"]);

            if (!empty($gradeIds)) {
                $chaptersToAdd = $chaptersToAdd->whereNotIn("grade_id", $gradeIds);
            }

            $chaptersToAdd = $chaptersToAdd->where("name", "LIKE", "%$query%")
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage - $chapters->count())
                ->orderByDesc("grade_id")
                ->offset($page * $perPage)
                ->get();

            $chapters = $chapters->merge($chaptersToAdd);
        }

        if ($notions->count() < $perPage) {
            $notionsToAdd = Notion::query()->where("name", "LIKE", "%$query%");

            if (!empty($gradeIds)) {
                $notionsToAdd = $notionsToAdd->whereHas(
                    "chapter.grade",
                    function (Builder $query) use ($gradeIds) {
                        return $query->whereNotIn("id", $gradeIds);
                    }
                );
            }

            $notionsToAdd = $notionsToAdd->with([
                "chapter.discipline",
                "chapter.grade" => function (BelongsTo $query) use ($gradeIds) {
                    return $query->orderByDesc("id");
                },
                "chapter.grade.country"
            ])
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage - $notions->count())
                ->offset($page * $perPage)
                ->get();

            $notions = $notions->merge($notionsToAdd);
        }

        return new JsonResponse([
            "page" => $page,
            "perPage" => $perPage,
            "data" => [
                "videos" => $videos,
                "chapters" => $chapters,
                "notions" => $notions,
            ],
        ]);
    }
}
