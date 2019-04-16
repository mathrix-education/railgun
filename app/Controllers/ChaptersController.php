<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\Grade;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
     * GET /chapters/{chapterId}/recursive
     * Get a chapter and all its visible children (notions, activities, modules).
     *
     * @param Request $request
     * @param int $chapterId The chapter id
     *
     * @return JsonResponse
     */
    public function recursive(Request $request, int $chapterId)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user === null) {
            return new JsonResponse(Chapter::recursive($chapterId));
        }

        /** @var array $chapterData */
        $chapterData = Cache::remember(
            "user.{$user->id}.chapter.{$chapterId}.recursive",
            60,
            function () use ($chapterId) {
                return Chapter::recursive($chapterId)->toArray();
            });

        return new JsonResponse($chapterData);
    }


    /**
     * GET /chapters/by-module/{moduleId}/recursive
     *
     * @param Request $request
     * @param int $moduleId The module id.
     *
     * @return JsonResponse
     */
    public function byModuleRecursive(Request $request, int $moduleId)
    {
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);

        return $this->recursive($request, $module->activity->notion->chapter_id);
    }


    /**
     * GET /chapters/by-grade-discipline/{gradeId}/{disciplineId}[/{scope}]
     * Get chapters by class and discipline ids
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $gradeId The class id.
     * @param int $disciplineId The discipline id.
     * @param string|null $scope The scope, use 'all' to display invisible chapters.
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
        $restrictToVisible = $scope !== "all" || !($request->user() !== null && $request->user()->tokenCan("redactor"));

        if ($restrictToVisible) {
            $query = $query->where("visible", "=", true);
        }

        $chapters = $query->orderBy("order")->get();

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
}
