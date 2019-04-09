<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;
use stdClass;

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
        $query = trim(mb_strtolower(urldecode($query))); // Trim and decode query
        $query = implode("%%", explode(" ", $query));
        $query = "%$query%";

        $chaptersQuery = Chapter::query()
            ->where("grade_id", "=", $gradeId)
            ->where("name", "LIKE", $query)
            ->where("visible", "=", true);

        $chapters = $chaptersQuery->limit($perPage)
            ->offset($page * $perPage)
            ->get();

        $activities = Activity::query()
            ->whereHas("notion.chapter", function (Builder $query) use ($gradeId) {
                $query->where("grade_id", "=", $gradeId);
            })
            ->where("name", "LIKE", $query)
            ->where("visible", "=", true)
            ->get(["id", "name"]);

        $activitiesMappings = [];
        foreach ($activities as $k => $v) {
            $activitiesMappings[$v->id] = $v->name;
        }

        $videosQuery = Module::query()
            ->whereIn("activity_id", $activities->pluck("id")->toArray())
            ->where("content", "REGEXP", "\\[video(.*)\\[/video\\]");
        $videos = $videosQuery
            ->limit($perPage)
            ->offset($page * $perPage)
            ->get(["id", "content", "activity_id"])
            ->map(function (Module $module) use ($activitiesMappings) {
                preg_match("/\[video(.*)\](.*)\[\/video\]/", $module->content, $matches);

                $data = new stdClass();
                $data->module_id = $module->id;
                $data->activity_name = $activitiesMappings[$module->activity_id];
                $data->youtube_id = isset($matches[2]) ? $matches[2] : null;

                return $data;
            });

        return new JsonResponse([
            "page" => $page,
            "per_page" => $perPage,
            "chapters_total" => $chaptersQuery->count(),
            "videos_total" => $videosQuery->count(),
            "chapters" => $chapters,
            "videos" => $videos
        ]);
    }
}
