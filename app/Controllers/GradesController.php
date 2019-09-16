<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\Discipline;
use Illuminate\Database\Eloquent\Builder;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Serve routes with /grades
 */
class GradesController extends BaseController
{
    /**
     * GET /grades/{gradeId}/disciplines/visible
     * Get visible disciplines which belong to a given grade id.
     *
     * @param int $gradeId The grade id.
     *
     * @return PaginationJsonResponse The disciplines list.
     *
     * @throws Http400BadRequestException
     */
    public function disciplinesVisible(int $gradeId): PaginationJsonResponse
    {
        $query = Discipline::query()
            ->whereHas(
                'chapters',
                static function (Builder $query): void {
                    $query->where('chapters.visible', '=', true);
                }
            )
            ->whereHas(
                'grades',
                static function (Builder $query) use ($gradeId): void {
                    $query->where('grades.id', '=', $gradeId);
                }
            );

        return new PaginationJsonResponse($query);
    }

    /**
     * GET /grades/slug/{gradeSlug}/chapters/slug/{chapterSlug}
     * Get a chapter by its slug and grade slug.
     *
     * @param string $chapterSlug The chapter slug.
     * @param string $gradeSlug   The grade slug.
     *
     * @return SuccessJsonResponse The chapter.
     */
    public function getBySlugChapterSlug(string $gradeSlug, string $chapterSlug): SuccessJsonResponse
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()
            ->whereHas(
                'grade',
                static function (Builder $query) use ($gradeSlug): void {
                    $query->where('slug', '=', $gradeSlug);
                }
            )
            ->where('slug', '=', $chapterSlug)
            ->firstOrFail();

        return new SuccessJsonResponse($chapter);
    }

    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters
     * Get chapters by class and discipline ids.
     *
     * @param int $gradeId      The grade id.
     * @param int $disciplineId The discipline id.
     *
     * @return PaginationJsonResponse The chapters list.
     *
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
     * @param int $gradeId      The class id.
     * @param int $disciplineId The discipline id.
     *
     * @return PaginationJsonResponse The chapters list.
     *
     * @throws Http400BadRequestException
     */
    public function chaptersByDisciplineVisible(int $gradeId, int $disciplineId): PaginationJsonResponse
    {
        $query = Chapter::byGradeAndDiscipline($gradeId, $disciplineId)
            ->where('visible', '=', true);

        return new PaginationJsonResponse($query);
    }
}
