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
 * @since 0.8.1
 * @copyright Mathrix
 */
class ModuleController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /modules Index
     * @apiGroup Modules
     * @apiName ModulesIndex
     * @apiDescription Retrieve modules
     * @apiPermission moderator
     * @apiUse ModuleArray
     * @return JsonResponse
     * @apiUse Http400InvalidRequest_Pagination
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /modules/{id} Get
     * @apiGroup Modules
     * @apiName ModuleGet
     * @apiDescription Get a module by id
     * @apiPermission public
     * @apiParam {int} id The module id
     * @apiUse Module
     * @apiUse CommentArray
     * @apiUse Http404ModelNotFound
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(int $id): JsonResponse
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

        return response()->json($module);
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /modules Add
     * @apiGroup Modules
     * @apiName ModuleAdd
     * @apiDescription Add a module
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse ModuleData
     * @apiUse Module
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 0.8.1
     * @api {patch} /modules/{id} Edit
     * @apiGroup Modules
     * @apiName ModuleEdit
     * @apiDescription Edit a module
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The module id
     * @apiUse ModuleData
     * @apiUse Module
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 0.8.1
     * @api {delete} /modules/{id} Delete
     * @apiGroup Modules
     * @apiName ModuleDelete
     * @apiDescription Delete a module
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The module id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */
}
