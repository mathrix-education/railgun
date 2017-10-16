<?php
namespace App\Http\Controllers;

use App\Models\Class_;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ClassController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class ClassController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /classes Index
     * @apiGroup Classes
     * @apiName ClassesIndex
     * @apiDescription Retrieve classes
     * @apiPermission public
     * @apiUse ClassArray
     */

    /**
     * @apiVersion 0.8.1
     * @api {get} /classes/{id} Get
     * @apiGroup Classes
     * @apiName ClassGet
     * @apiDescription Get a class by id
     * @apiPermission public
     * @apiParam {int} id The class id
     * @apiUse Class
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $class = Class_::where('id', $id)->firstOrFail();

        return $this->http200Success($class);
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /classes Add
     * @apiGroup Classes
     * @apiName ClassAdd
     * @apiDescription Add class
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse ClassData
     * @apiUse Class
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
        if ($request->user()->cant('add', Class_::class)) {
            return $this->http401Unauthorized();
        }

        $class = new Class_();
        $class->fill($request->all());

        if (!$class->save()) {
            return $class->handleResponse($this->http422Save());
        }

        return $this->http200Success($class);
    }


    /**
     * @apiVersion 0.8.1
     * @api {patch} /classes/{id} Edit
     * @apiGroup Classes
     * @apiName ClassEdit
     * @apiDescription Edit a class
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The class id
     * @apiUse ClassData
     * @apiUse Class
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
        /** @var Class_ $class */
        $class = Class_::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', $class)) {
            return $this->http401Unauthorized();
        }

        $class->fill($request->all());

        if (!$class->save()) {
            return $class->handleResponse($this->http422Save());
        }

        return $this->http200Success($class);
    }


    /**
     * @apiVersion 0.8.1
     * @api {delete} /classes/{id} Delete (Not implemented)
     * @apiGroup Classes
     * @apiName ClassDelete
     * @apiDescription Delete a class
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The class id
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function delete(int $id)
    {
        return $this->http501NotImplemented();
    }
}
