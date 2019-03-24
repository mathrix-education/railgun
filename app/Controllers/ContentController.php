<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Notion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class ContentController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ContentController extends BaseController
{
    /**
     * Global research.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $query The query string.
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     *
     * TODO: Need deep rework
     */
    public function searchGlobal(Request $request, string $query, int $page = 0, $perPage = 100)
    {
        $query = trim(mb_strtolower(urldecode($query))); // Trim and decode query
        $query = implode("%%", explode(" ", $query));

        $user = $request->user();

        $chapters = new Collection();
        $notions = new Collection();
        $classIds = [];

        // Videos
        $videos = Activity::where("visible", true)
            ->where("name", "LIKE", "%$query%")
            ->orWhere("slug", "LIKE", "%$query%")
            ->whereHas("modules", function (Builder $q) use ($query) {
                return $q->where("type", "course")
                    ->where("content", "LIKE", "%[video]%");
            })
            ->with([
                "notions", "notions.chapters",
                "notions.chapters.discipline",
                "notions.chapters.class_",
                "notions.chapters.class_.country", "modules" => function (BelongsToMany $q) use ($query) {
                    return $q->where("type", "course")
                        ->where("content", "LIKE", "%[video]%");
                }
            ])
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        // Custom requests for logged users
        if ($user && $user->class_id !== null) {
            /** @var Grade $userClass */
            $userClass = Grade::find($user->class_id);
            $cursus = $userClass->cursus();
            $classIds = $cursus->isNotEmpty() ? $cursus->pluck("id")
                ->toArray() : [];

            $chapters = Chapter::whereIn("class_id", $classIds)
                ->with(["discipline", "class_", "class_.country"])
                ->where("name", "LIKE", "%$query%")
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage)
                ->orderByDesc("class_id")
                ->offset(($page - 1) * $perPage)
                ->get();

            $notions = Notion::where("name", "LIKE", "%$query%")
                ->whereHas("chapters.class_", function (Builder $query) use ($classIds) {
                    return $query->whereIn("id", $classIds);
                })
                ->with(["chapters.discipline", "chapters.class_" => function (BelongsTo $query) use ($classIds) {
                    return $query->orderByDesc("id");
                }, "chapters.class_.country"])
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get();
        }

        // Not logged user or not filled collection
        if ($chapters->count() < $perPage) {
            $chaptersToAdd = Chapter::with(["discipline", "class_", "class_.country"]);

            if (!empty($classIds)) {
                $chaptersToAdd = $chaptersToAdd->whereNotIn("class_id", $classIds);
            }

            $chaptersToAdd = $chaptersToAdd->where("name", "LIKE", "%$query%")
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage - $chapters->count())
                ->orderByDesc("class_id")
                ->offset(($page - 1) * $perPage)
                ->get();

            $chapters = $chapters->merge($chaptersToAdd);
        }

        if ($notions->count() < $perPage) {
            $notionsToAdd = Notion::where("name", "LIKE", "%$query%");

            if (!empty($classIds)) {
                $notionsToAdd = $notionsToAdd->whereHas("chapters.class_", function (Builder $query) use ($classIds) {
                    return $query->whereNotIn("id", $classIds);
                });
            }

            $notionsToAdd = $notionsToAdd->with(["chapters.discipline", "chapters.class_" => function (BelongsTo $query) use ($classIds) {
                return $query->orderByDesc("id");
            }, "chapters.class_.country"])
                ->orWhere("slug", "LIKE", "%$query%")
                ->limit($perPage - $notions->count())
                ->offset(($page - 1) * $perPage)
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
