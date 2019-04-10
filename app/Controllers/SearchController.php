<?php

namespace App\Controllers;

use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
     * @param int $gradeId The grade id.
     * @param string $query The search query.
     * @param int $page The page.
     * @param int $perPage The number of results (videos, chapters, notions) per page.
     *
     * @return JsonResponse
     */
    public function content(int $gradeId, string $query, int $page = 0, int $perPage = 100)
    {
        // Trim, decode query
        $query = trim(mb_strtolower(urldecode($query)));
        $query = implode("%%", explode(" ", $query));
        $query = "%$query%";


        $videosQuery = Module::query()
            ->select(["id", "content", "seo_slug", "activity_id"])
            ->with([
                "activity:id,name,notion_id",
                "activity.notion:id,chapter_id",
                "activity.notion.chapter:id,slug,discipline_id",
                "activity.notion.chapter.discipline:id,slug"
            ])
            ->whereHas("activity", function (Builder $q) use ($query) {
                $q->where("name", "LIKE", $query);
            })
            ->whereHas("activity.notion.chapter", function (Builder $q) use ($gradeId) {
                $q->where("grade_id", "=", $gradeId);
            })
            ->where("content", "REGEXP", "\\[video(.*)\\[/video\\]");

        $videos = $videosQuery->limit($perPage)
            ->offset($page * $perPage)
            ->get()
            ->map(function (Module $data) {
                preg_match("/\[video.*\](.*)\[\/video\]/", $data->content, $matches);

                return (object)[
                    "discipline_slug" => $data->activity->notion->chapter->discipline->slug,
                    "chapter_slug" => $data->activity->notion->chapter->slug,
                    "module_seo_slug" => $data->seo_slug,
                    "module_id" => $data->id,
                    "activity_name" => $data->activity->name,
                    "youtube_id" => !empty($matches[1]) ? $matches[1] : null
                ];
            });

        return new JsonResponse([
            "page" => $page,
            "per_page" => $perPage,
            "total" => $videosQuery->count(),
            "data" => $videos
        ]);
    }
}
