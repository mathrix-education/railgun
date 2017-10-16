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
     *
     * @param Request $request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $page = 1, int $perPage = 10)
    {
        if ($page < 1 || $perPage < 1) {
            return $this->http400InvalidRequest('page and perPage have to be strictly positive');
        }

        return $this->http200Success(
            Course::limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->orderByDesc('created_at')
                ->get()
        );
    }


    /**
     * @apiVersion 0.8.1
     * @api {get} /courses/{id} Get
     * @apiGroup Courses
     * @apiName CourseGet
     * @apiDescription Get a course by id
     * @apiPermission public
     * @apiParam {int} id The course id
     * @apiUse Course
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $course = Course::where('id', $id)->firstOrFail();

        return $this->http200Success($course);
    }


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
     * @apiUse 401Unauthorized
     * @apiUse 422Validation
     * @apiUse 422Save
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        if ($request->user()->cant('add', Course::class)) {
            return $this->http401Unauthorized();
        }

        $course = new Course();
        $course->fill($request->all());

        if (!$course->save()) {
            return $course->handleResponse($this->http422Save());
        }

        return $this->http200Success($course);
    }


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
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     * @apiUse 422Save
     * @apiUse 422Validation
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id)
    {
        /** @var Course $course */
        $course = Course::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', $course)) {
            return $this->http401Unauthorized();
        }

        $course->fill($request->all());

        if (!$course->save()) {
            return $course->handleResponse($this->http422Save());
        }

        return $this->http200Success($course);
    }


    /**
     * @apiVersion 0.8.1
     * @api {delete} /courses/{id} Delete
     * @apiGroup Courses
     * @apiName CourseDelete
     * @apiDescription Delete a course
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The course id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        /** @var Course $course */
        $course = Course::where('id', $id)->firstOrFail();

        if ($request->user()->cant('delete', $course)) {
            return $this->http401Unauthorized();
        }

        if (!$course->delete()) {
            return $course->handleResponse($this->http422Delete());
        }

        return $this->http200Success();
    }
}
