<?php
namespace App\Http\Controllers;

use App\Models\Class_;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class ClassController
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ClassController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /classes Index
     * @apiGroup Classes
     * @apiName ClassesIndex
     * @apiDescription Retrieve classes
     * @apiPermission public
     * @apiUse ClassArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Class_::all());
    }


    /**
     * @apiVersion 1.0.0
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
     * @apiVersion 1.0.0
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
     * @apiVersion 1.0.0
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
     * @apiVersion 1.0.0
     * @api {delete} /classes/{id} Delete (NYI)
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
