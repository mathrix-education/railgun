<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;

/**
 * Class ChaptersController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ChaptersController extends BaseController
{
    /**
     * Get a chapter and all its visible children (notions, activities, modules).
     *
     * @param int $id The chapter id
     *
     * @return JsonResponse
     */
    public function recursive(int $id)
    {
        $chapter = Chapter::with([
            "notions" => function (BelongsToMany $q) {
                $q->where("visible", "=", true);
            },
            "notions.activities" => function (BelongsToMany $q) {
                $q->where("visible", "=", true);
            },
            "notions.activities.modules" => function (BelongsToMany $q) {
                $q->where("visible", "=", true);
            }
        ])
            ->findOrFail($id);

        return new JsonResponse($chapter);
    }


    /**
     * GET /chapters/by-grade-discipline/{grade_id}/{discipline_id}
     *
     * Get chapters by class and discipline ids
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $gradeId The class id
     * @param int $disciplineId The discipline id
     * @param string|null $scope The scope, use 'all' to display invisible chapters
     *
     * @return JsonResponse
     */
    public function byGradeIdDisciplineId(Request $request, int $gradeId, int $disciplineId, string $scope = null)
    {
        $query = Chapter::query()->where([
            ["grade_id", "=", $gradeId],
            ["discipline_id", "=", $disciplineId],
        ]);

        // Check if all chapters should be returned.
        $restrictToVisible = $scope !== "all" || !($request->user() !== null && $request->user()->isModerator());

        if ($restrictToVisible) {
            $query = $query->where("visible", "=", true);
        }

        $chapters = $query->orderBy("order")
            ->get();

        return new JsonResponse($chapters);
    }


    /**
     * GET /chapters/slug/{chapterSlug}/by-grade-slug/{gradeSlug}
     * Get a chapter by its slug and grade slug.
     *
     * @param string $chapterSlug The chapter slug.
     * @param string $gradeSlug The grade slug.
     *
     * @return JsonResponse
     */
    public function getBySlugGradeSlug(string $chapterSlug, string $gradeSlug): JsonResponse
    {
        $grade = Grade::query()
            ->where("slug", "=", $gradeSlug)
            ->firstOrFail();

        $chapter = Chapter::query()
            ->where([
                ["class_id", "=", $grade->id],
                ["slug", "=", $chapterSlug],
            ])
            ->firstOrFail();

        return new JsonResponse($chapter);
    }
}
