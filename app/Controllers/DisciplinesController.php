<?php

namespace App\Controllers;

use App\Models\Discipline;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class DisciplinesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class DisciplinesController extends BaseController
{
    /**
     * GET /disciplines/slug/{disciplineSlug}
     * Get a discipline by its slug.
     *
     * @param string $disciplineSlug The discipline slug.
     *
     * @return JsonResponse
     */
    public function getBySlug(string $disciplineSlug)
    {
        $discipline = Discipline::query()
            ->where("slug", "=", $disciplineSlug)
            ->firstOrFail();

        return new JsonResponse($discipline);
    }


    /**
     * GET /disciplines/by-grade/{gradeId}/{page}/{perPage}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $gradeId
     * @param int $page
     * @param int $perPage
     *
     * @return PaginationJsonResponse
     */
    public function byGrade(Request $request, int $gradeId, int $page, int $perPage)
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Grade $grade */
        $grade = Grade::query()->findOrFail($gradeId);
        $allowedToSeeAll = $user !== null && $user->tokenCan("moderator");
        $wantsToSeeAll = $request->hasHeader("X-Scope") && $request->header("X-Scope") === "all";

        $query = $grade->disciplines();

        if (!$allowedToSeeAll || !$wantsToSeeAll) {
            $query = $query->wherePivot("visible", "=", true);
        }


        return new PaginationJsonResponse($query, $page, $perPage);
    }
}
