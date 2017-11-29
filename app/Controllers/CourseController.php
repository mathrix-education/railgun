<?php
namespace App\Controllers;




/**
 * Class CourseController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix
 */
class CourseController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /courses/{?page}/{?perPage} Index
     * @apiGroup Courses
     * @apiName CoursesIndex
     * @apiDescription Retrieve courses
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse CourseArray
     */


    /**
     * @apiVersion 1.0.0
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
     * @apiVersion 1.0.0
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
     * @apiVersion 1.0.0
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
     * @apiVersion 1.0.0
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
