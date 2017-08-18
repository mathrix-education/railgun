<?php
namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Module ModuleController
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class ModuleController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {get} /modules Index
     * @apiGroup Modules
     * @apiName ModulesIndex
     * @apiDescription Retrieve modules
     * @apiPermission public
     * @apiUse ModuleArray
     * @return JsonResponse
     */
    public function index()
    {
        return $this->http200Success(Module::all());
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /modules/{id} Get
     * @apiGroup Modules
     * @apiName ModuleGet
     * @apiDescription Get a module by id
     * @apiPermission public
     * @apiParam {int} id The module id
     * @apiUse Module
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $module = Module::where('id', $id)->firstOrFail();

        return $this->http200Success($module);
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /modules Add
     * @apiGroup Modules
     * @apiName ModuleAdd
     * @apiDescription Add a module
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse ModuleData
     * @apiUse Module
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
        if ($request->user()->cant('add')) {
            return $this->http401Unauthorized();
        }

        $module = new Module();
        $module->fill($request->all());

        if (!$module->save()) {
            return $module->handleResponse($this->http422Save());
        }

        return $this->http200Success($module);
    }


    /**
     * @apiVersion 1.0.0
     * @api {patch} /modules/{id} Edit
     * @apiGroup Modules
     * @apiName ModuleEdit
     * @apiDescription Edit a module
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The module id
     * @apiUse ModuleData
     * @apiUse Module
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
        /** @var Module $module */
        $module = Module::where('id', $id)->firstOrFail();

        if ($request->user()->cant('edit', Module::class)) {
            return $this->http401Unauthorized();
        }

        $module->fill($request->all());

        if (!$module->save()) {
            return $module->handleResponse($this->http422Save());
        }

        return $this->http200Success($module);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /modules/{id} Delete (NYI)
     * @apiGroup Modules
     * @apiName ModuleDelete
     * @apiDescription Delete a module
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The module id
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
