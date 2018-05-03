<?php
namespace App\Controllers;

use App\Exceptions\Http404ModelNotFoundException;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ChapterController
 *
 * @author Mathrix Back-End Team
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
     * @throws \Exception
     */
    public function recursive(Request $request, int $id)
    {
        /** @var Chapter $data */
        $data = Chapter::with([
            "notions" => function (BelongsToMany $q) {
                return $q->where("visible", 1);
            },
            "notions.activities" => function (BelongsToMany $q) {
                return $q->where("visible", 1);
            },
            "notions.activities.modules" => function (BelongsToMany $q) {
                return $q->where("visible", 1);
            }
        ])
            ->where("id", $id)
            ->first();

        /** @var User $user */
        $user = $request->user();

        if (!$data->visible && $user === null || $user->type === "registered") {
            throw new Http404ModelNotFoundException();
        } else {
            return response()->json($data);
        }
    }
}
