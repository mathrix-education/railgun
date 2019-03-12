<?php

namespace App\Controllers;

use App\Models\Chapter;
use App\Models\Class_;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ChapterController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class ChapterController extends BaseController
{
    /**
     * Get chapters by class and discipline slugs
     *
     * @param Request $request The request
     * @param int $class_id The class id
     * @param int $discipline_id The discipline id
     * @param string|null $scope The scope, use 'all' to display invisible chapters
     *
     * @return JsonResponse
     */
    public function by_class_discipline(Request $request, int $class_id, int $discipline_id, string $scope = null)
    {
        $query = Chapter::where([
            ["class_id", "=", $class_id],
            ["discipline_id", "=", $discipline_id],
        ]);

        // Check if all chapters should be returned.
        $restrictToVisible = $scope !== "all" || !($request->user() !== null && $request->user()->isModerator());

        if ($restrictToVisible) {
            $query = $query->where("visible", "=", true);
        }

        $chapters = $query->orderBy("order")
            ->get();

        $response = new JsonResponse($chapters);

        return $response;
    }


    /**
     * Get a chapter by its slug and class slug.
     *
     * @param string $chapterSlug The chapter slug.
     * @param string $classSlug The class slug.
     *
     * @return JsonResponse
     */
    public function getBySlugAndClassSlug(string $chapterSlug, string $classSlug): JsonResponse
    {
        $class = Class_::where("slug", "=", $classSlug)
            ->firstOrFail();
        $chapter = Chapter::where([
            ["class_id", "=", $class->id],
            ["slug", "=", $chapterSlug],
        ])
            ->firstOrFail();

        return new JsonResponse($chapter);
    }


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

        $response = new JsonResponse($chapter);

        return $response;
    }
}
