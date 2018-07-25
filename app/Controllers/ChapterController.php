<?php
namespace App\Controllers;

use App\Exceptions\Http404ModelNotFoundException;
use App\Models\Chapter;
use App\Models\Class_;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ChapterController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class ChapterController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /chapters/{?page}/{?perPage} Index
     * @apiGroup Chapters
     * @apiName ChaptersIndex
     * @apiDescription Retrieve chapters
     * @apiPermission moderator
     * @apiUse Pagination
     * @apiUse ChapterArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /chapters/{id} Get
     * @apiGroup Chapters
     * @apiName ChapterGet
     * @apiDescription Get a chapter by id
     * @apiPermission public
     * @apiParam {int} id The chapter id
     * @apiUse ChapterModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {post} /chapters Add
     * @apiGroup Chapters
     * @apiName ChapterAdd
     * @apiDescription Add a chapter
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse ChapterRequest
     * @apiUse ChapterModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 4.0.0
     * @api {patch} /chapters/{id} Edit
     * @apiGroup Chapters
     * @apiName ChapterEdit
     * @apiDescription Edit a chapter
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The chapter id
     * @apiUse ChapterRequest
     * @apiUse ChapterModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 4.0.0
     * @api {delete} /chapters/{id} Delete
     * @apiGroup Chapters
     * @apiName ChapterDelete
     * @apiDescription Delete a chapter
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The chapter id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /chapters/by-class-discipline/{class_id}/{discipline_id} By Class and Discipline
     * @apiGroup Chapters
     * @apiName ChapterClassDiscipline
     * @apiDescription Get chapters by class and discipline
     * @apiUse Bearer
     * @apiPermission public
     * @apiParam {int} class_id The class id
     * @apiParam {int} discipline_id The discipline id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param int $class_id
     * @param int $discipline_id
     *
     * @return JsonResponse
     */
    public function by_class_discipline(int $class_id, int $discipline_id)
    {
        $chapters = Chapter::where([["class_id", $class_id], ["discipline_id", $discipline_id]])
            ->orderBy("order")
            ->get();

        return response()->json($chapters);
    }


    /**
     * @apiVersion 4.0.4
     * @api {get} /chapters/slug/{chapter_slug}/by-class-slug/{class_slug} Get by slug and class slug
     * @apiGroup Chapters
     * @apiName ChapterBySlugAndClassDiscipline
     * @apiDescription Get chapters by class and discipline
     * @apiUse Bearer
     * @apiPermission public
     * @apiParam {string} chapter_slug The chapter slug
     * @apiParam {string} class_slug The class slug
     * @apiUse ChapterModel
     * @apiUse Http404ModelNotFound
     *
     * @param string $chapterSlug
     * @param string $classSlug
     *
     * @return JsonResponse
     */
    public function getBySlugAndClassSlug(string $chapterSlug, string $classSlug): JsonResponse
    {
        $class = Class_::where("slug", "=", $classSlug)->firstOrFail();
        $chapter = Chapter::where([
            ["class_id", "=", $class->id],
            ["slug", "=", $chapterSlug]
        ])->with([])->firstOrFail();

        return new JsonResponse($chapter);
    }


    /**
     * @apiVersion 4.0.0
     * @api {get} /chapter/recursive/{id} Recursive
     * @apiGroup Chapters
     * @apiName ChapterRecursive
     * @apiDescription Get a chapter and its linked models recursiveley
     * @apiPermission public
     * @apiParam {int} id the chapter id
     * @apiUse ChapterArray
     *
     * @param Request $request
     * @param int $id
     * => function($q) {
     * return  $q->where(["visible", 1]);
     * }
     *
     * @return JsonResponse
     * @throws Http404ModelNotFoundException
     */
    public function recursive(Request $request, int $id)
    {
        /** @var Chapter $data */
        $data = Chapter::with([
            "notions" => function (BelongsToMany $q) {
                return $q->where("visible", 1)->orderBy("chapter_notion.order");
            },
            "notions.activities" => function (BelongsToMany $q) {
                return $q->where("visible", 1)->orderBy("activity_notion.order");
            },
            "notions.activities.modules" => function (BelongsToMany $q) {
                return $q->where("visible", 1)->orderBy("activity_module.order");
            }
        ])
            ->where("id", $id)
            ->firstOrFail();


        return response()->json($data);
    }
}
