<?php
namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CourseController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class CourseController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /courses Index
     * @apiGroup Courses
     * @apiName CoursesIndex
     * @apiDescription Retrieve courses
     * @apiPermission public
     * @apiParam {int} [page=1] The page number
     * @apiParam {int} [perPage=10] The course count per page
     * @apiUse CourseArray
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /courses/{id} Get
     * @apiGroup Courses
     * @apiName CourseGet
     * @apiDescription Get a course by id
     * @apiPermission public
     * @apiParam {int} id The course id
     * @apiUse Course
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 0.8.1
     * @api {course} /courses Add
     * @apiGroup Courses
     * @apiName CourseAdd
     * @apiDescription Add a course
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse CourseData
     * @apiUse Course
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 0.8.1
     * @api {patch} /courses/{id} Edit
     * @apiGroup Courses
     * @apiName CourseEdit
     * @apiDescription Edit a course
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The course id
     * @apiUse CourseData
     * @apiUse Course
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 0.8.1
     * @api {delete} /courses/{id} Delete
     * @apiGroup Courses
     * @apiName CourseDelete
     * @apiDescription Delete a course
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The course id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
