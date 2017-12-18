<?php
namespace App\Controllers;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class LessonController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.1
 * @copyright Mathrix SA
 */
class LessonController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /lessons/{?page}/{?perPage} Index
     * @apiGroup Lessons
     * @apiName LessonsIndex
     * @apiDescription Retrieve lessons
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse LessonArray
     *
     * @param Request $request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function index(Request $request, int $page = 1, int $perPage = 10): JsonResponse
    {
        // Check permission
        if ($request->user() !== null && $request->user()->cant("index", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        return response()->json(
            Lesson::with("module")
                ->limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get()
        );
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /lessons/{id} Get
     * @apiGroup Lessons
     * @apiName LessonGet
     * @apiDescription Get a lesson by id
     * @apiPermission public
     * @apiParam {int} id The lesson id
     * @apiUse LessonModel
     * @apiUse Http404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
    {
        $model = Lesson::with("module")->where("id", $id)->firstOrFail();

        return response()->json($model);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /lessons Add
     * @apiGroup Lessons
     * @apiName LessonAdd
     * @apiDescription Add a lesson
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse LessonRequest
     * @apiUse LessonModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */

    /**
     * @apiVersion 1.0.0
     * @api {patch} /lessons/{id} Edit
     * @apiGroup Lessons
     * @apiName LessonEdit
     * @apiDescription Edit a lesson
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The lesson id
     * @apiUse LessonRequest
     * @apiUse LessonModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 1.0.0
     * @api {delete} /lessons/{id} Delete
     * @apiGroup Lessons
     * @apiName LessonDelete
     * @apiDescription Delete a lesson
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The lesson id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
