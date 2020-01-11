<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Grade;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use function explode;
use function implode;
use function mb_strtolower;
use function preg_match;
use function trim;
use function urldecode;

/**
 * Serve routes starting with /search.
 */
class SearchController extends BaseController
{
    /**
     * GET /search/{query}
     * Search in content with a single query.
     *
     * @param int    $gradeId The grade id.
     * @param string $query   The search query.
     *
     * @return SuccessJsonResponse The search results.
     */
    public function content(int $gradeId, string $query): SuccessJsonResponse
    {
        [$page, $perPage] = PaginationJsonResponse::getPagination();

        /** @var Grade $grade */
        $grade    = Grade::query()
            ->find($gradeId);
        $gradeIds = $grade->cursus()
            ->pluck('id')
            ->toArray();

        // Trim, decode query
        $query = mb_strtolower(trim(urldecode($query)));
        $query = implode('%%', explode(' ', $query));
        $query = "%$query%";

        $videosQuery = Module::query()
            ->select(['id', 'content', 'seo_slug', 'activity_id'])
            ->with([
                'activity:id,name,notion_id',
                'activity.notion:id,chapter_id',
                'activity.notion.chapter:id,slug,discipline_id',
                'activity.notion.chapter.discipline:id,slug',
            ])
            ->whereHas(
                'activity',
                static function (Builder $q) use ($query): void {
                    $q->where('name', 'LIKE', $query);
                }
            )
            ->whereHas(
                'activity.notion.chapter',
                static function (Builder $q) use ($gradeIds): void {
                    $q->whereIn('grade_id', $gradeIds);
                }
            )
            ->where('content', 'REGEXP', '\\[video(.*)\\[/video\\]');

        $videos = $videosQuery->limit($perPage)
            ->offset($page * $perPage)
            ->get()
            ->map(static function (Module $data) {
                preg_match('/\[video.*](.*)\[\/video]/', $data->content, $matches);

                return (object)[
                    'discipline_slug' => $data->activity->notion->chapter->discipline->slug,
                    'chapter_slug'    => $data->activity->notion->chapter->slug,
                    'module_seo_slug' => $data->seo_slug,
                    'module_id'       => $data->id,
                    'activity_name'   => $data->activity->name,
                    'youtube_id'      => !empty($matches[1]) ? $matches[1] : null,
                ];
            });

        return new SuccessJsonResponse($videos, [
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $videosQuery->count(),
        ]);
    }
}
