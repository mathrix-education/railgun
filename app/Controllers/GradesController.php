<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\Discipline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

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
     * GET /grades/{gradeId}/disciplines/visible
     *
     * @param int $gradeId The grade id.
     *
     * @return PaginationJsonResponse
     * @throws Http400BadRequestException
     */
    public function disciplinesVisible(int $gradeId): PaginationJsonResponse
    {
        $query = Discipline::query()
            ->whereHas("chapters", function (Builder $query) use ($gradeId) {
                $query->where("chapters.visible", "=", true);
            })
            ->whereHas("grades", function (Builder $query) use ($gradeId) {
                $query->where("grades.id", "=", $gradeId);
            });

        return new PaginationJsonResponse($query);
    }


    /**
     * GET /grades/slug/{gradeSlug}/chapters/slug/{chapterSlug}
     * Get a chapter by its slug and grade slug.
     *
     * @param string $chapterSlug The chapter slug.
     * @param string $gradeSlug The grade slug.
     *
     * @return SuccessJsonResponse
     */
    public function getBySlugChapterSlug(string $gradeSlug, string $chapterSlug): SuccessJsonResponse
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()
            ->whereHas("grade", function (Builder $query) use ($gradeSlug) {
                $query->where("slug", "=", $gradeSlug);
            })
            ->where("slug", "=", $chapterSlug)
            ->firstOrFail();

        return new SuccessJsonResponse($chapter);
    }


    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters
     * Get chapters by class and discipline ids.
     *
     * @param int $gradeId The grade id.
     * @param int $disciplineId The discipline id.
     *
     * @return PaginationJsonResponse
     * @throws Http400BadRequestException
     */
    public function chaptersByDiscipline(int $gradeId, int $disciplineId): PaginationJsonResponse
    {
        $query = Chapter::byGradeAndDiscipline($gradeId, $disciplineId);

        return new PaginationJsonResponse($query);
    }


    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters/visible
     * Get visible chapters by class and discipline ids.
     *
     * @param int $gradeId The class id.
     * @param int $disciplineId The discipline id.
     *
     * @return PaginationJsonResponse
     * @throws Http400BadRequestException
     */
    public function chaptersByDisciplineVisible(int $gradeId, int $disciplineId): PaginationJsonResponse
    {
        $query = Chapter::byGradeAndDiscipline($gradeId, $disciplineId)
            ->where("visible", "=", true);

        return new PaginationJsonResponse($query);
    }
}
