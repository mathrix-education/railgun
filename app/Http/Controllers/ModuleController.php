<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ModuleController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.9.1
 * @copyright Mathrix
 */
class ModuleController extends BaseController
{
    /**
     * @apiVersion 0.9.1
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
     * @apiVersion 0.9.1
     * @api {get} /modules/{id} Get
     * @apiGroup Modules
     * @apiName ModuleGet
     * @apiDescription Get a module by id
     * @apiPermission public
     * @apiParam {int} id The module id
     * @apiUse Module
     * @apiUse CommentArray
     * @apiUse 404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id)
    {
        $module = Module::where('id', $id)->with([
            'comments' => function (MorphMany $query) use ($id) {
                $query->getQuery()
                    ->where('commentable_id', '=', $id)
                    ->where('commentable_type', '=', 'module')
                    ->orderByDesc('created_at');
            },
            'comments.children' => function (HasMany $query) {
                $query->getQuery()->orderBy('created_at');
            }
        ])->firstOrFail();

        return $this->http200Success($module);
    }


    /**
     * @apiVersion 0.9.1
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
        if ($request->user()->cant('add', Module::class)) {
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
     * @apiVersion 0.9.1
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
     * @apiVersion 0.9.1
     * @api {delete} /modules/{id} Delete
     * @apiGroup Modules
     * @apiName ModuleDelete
     * @apiDescription Delete a module
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The module id
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
        /** @var Module $module */
        $module = Module::where('id', '=', $id)->firstOrFail();

        if ($request->user()->cant('delete', $module)) {
            return $this->http401Unauthorized();
        }

        if (!$module->delete()) {
            return $module->handleResponse($this->http422Delete());
        }

        return $this->http200Success();
    }
}
