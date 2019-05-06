<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class GradesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class GradesController extends BaseController
{
    /**
     * GET /grades/slug/{gradeSlug}
     *
     * @param string $gradeSlug The grade slug.
     *
     * @return JsonResponse
     */
    public function getBySlug(string $gradeSlug)
    {
        $grade = Grade::query()
            ->where("slug", "=", $gradeSlug)
            ->firstOrFail();

        return new JsonResponse($grade);
    }


    /**
     * GET /grades/slug/{gradeSlug:[a-z0-9\-]+}/chapters/slug/{chapterSlug:[a-z0-9\-]+}
     * Get a chapter by its slug and grade slug.
     *
     * @param string $chapterSlug The chapter slug.
     * @param string $gradeSlug The grade slug.
     *
     * @return JsonResponse
     */
    public function getBySlugChapterSlug(string $chapterSlug, string $gradeSlug): JsonResponse
    {
        /** @var Grade $grade */
        $grade = Grade::query()
            ->where("slug", "=", $gradeSlug)
            ->firstOrFail();

        /** @var Chapter $chapter */
        $chapter = Chapter::query()
            ->where([
                ["grade_id", "=", $grade->id],
                ["slug", "=", $chapterSlug],
            ])
            ->firstOrFail();

        return new JsonResponse($chapter);
    }


    /**
     * GET /grades/{gradeId}/disciplines/{page}/{perPage}[?scope=visible]
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $gradeId The grade id.
     * @param int $page The results page.
     * @param int $perPage The number of disciplines per page.
     *
     * @return PaginationJsonResponse
     */
    public function disciplines(Request $request, int $gradeId, int $page, int $perPage): PaginationJsonResponse
    {
        /** @var Grade $grade */
        $grade = Grade::query()->findOrFail($gradeId);
        $allowedToSeeAll = scope($request->user(), "disciplines:read");
        $wantsToSeeAll = $request->query("scope", "all") === "all";
        $restrictToVisible = !$allowedToSeeAll || !$wantsToSeeAll;

        $query = $grade->disciplines();

        if ($restrictToVisible) {
            $query = $query->where("chapters.visible", "=", true);
        }

        return new PaginationJsonResponse($query, $page, $perPage);
    }


    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters/{page}/{perPage}[?scope=visible]
     * Get chapters by class and discipline ids
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $gradeId The class id.
     * @param int $disciplineId The discipline id.
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function chaptersByDiscipline(Request $request, int $gradeId, int $disciplineId, int $page, int $perPage)
    {
        $scope = $request->query("scope", "all");
        $allowedToSeeAll = scope($request->user(), "chapters:read");
        $wantsToSeeAll = $scope === "all";
        $restrictToVisible = !$allowedToSeeAll || !$wantsToSeeAll;

        // Check if all chapters should be returned.
        $key = "grade.$gradeId.discipline.$disciplineId.chapters.$scope.$page.$perPage";

        return Cache::remember($key, 15,
            function () use ($gradeId, $disciplineId, $restrictToVisible, $page, $perPage) {
                $query = Chapter::query()->where([
                    ["grade_id", "=", $gradeId],
                    ["discipline_id", "=", $disciplineId],
                ])
                    ->orderBy("order");

                if ($restrictToVisible) {
                    $query = $query->where("visible", "=", true);
                }

                return new PaginationJsonResponse($query, $page, $perPage);
            });
    }
}
