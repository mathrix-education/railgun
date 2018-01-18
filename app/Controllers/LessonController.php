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
     */

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
     */


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
