<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Meta;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ModuleController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ModuleController extends BaseController
{
    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /modules/{page} Index
     * @apiGroup       Modules
     * @apiName        ModulesIndex
     * @apiDescription Retrieve modules
     * @apiPermission  moderator
     * @apiUse         ModuleArray
     * @apiUse         Http401Unauthorized
     *
     * @param mixed      $key
     * @param null|mixed $value
     *
     * @return JsonResponse
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {get} /modules/{id} Get
     * @apiGroup       Modules
     * @apiName        ModuleGet
     * @apiDescription Get a module by id
     * @apiPermission  public
     * @apiParam {int} id The module id
     * @apiUse         ModuleModel
     * @apiUse         CommentArray
     * @apiUse         Http404ModelNotFound
     *
     * @param Request $request
     * @param int     $key     the module id
     * @param null    $value   not used here
     *
     * @return JsonResponse
     */
    public function get(Request $request, $key, $value = null): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Module $module */
        $module = Module::where("id", $key)->firstOrFail();

        if ($user !== null) {
            $user->append(["premium", "braintree"]);
        }

        // Hide module content if user is not premium nor moderator
        if ($module->premium && ($user === null || !$user->premium && !$user->isModerator())) {
            $id = $module->id;
            $module = [
                "id" => $id,
                "premium" => true,
            ];
        }

        return response()->json($module);
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /modules Add
     * @apiGroup       Modules
     * @apiName        ModuleAdd
     * @apiDescription Add a module
     * @apiPermission  moderator
     * @apiUse         Bearer
     * @apiUse         ModuleArray
     * @apiUse         ModuleModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelValidation
     * @apiUse         Http422ModelSave
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {patch} /modules/{id} Edit
     * @apiGroup       Modules
     * @apiName        ModuleEdit
     * @apiDescription Edit a module
     * @apiPermission  moderator
     * @apiUse         Bearer
     * @apiParam {int} id The module id
     * @apiUse         ModuleArray
     * @apiUse         ModuleModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     * @apiUse         Http422ModelSave
     * @apiUse         Http422ModelValidation
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {delete} /modules/{id} Delete
     * @apiGroup       Modules
     * @apiName        ModuleDelete
     * @apiDescription Delete a module
     * @apiUse         Bearer
     * @apiPermission  moderator
     * @apiParam {int} id The module id
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     */

    /**
     * @apiVersion     4.0.5
     *
     * @api            {post} /modules/search Search
     * @apiGroup       Modules
     * @apiName        ModulesSearch
     * @apiDescription Search in modules
     * @apiPermission  moderator
     * @apiUse         Bearer
     * @apiUse         ModuleArray
     * @apiUse         Http400InvalidRequest
     * @apiUse         Http401Unauthorized
     */

    /**
     * @apiVersion     4.0.0
     *
     * @api            {patch} /modules/{id}/mark Mark
     * @apiGroup       Modules
     * @apiName        ModuleMark
     * @apiDescription Mark an module as completed or not
     * @apiUse         Bearer
     * @apiPermission  registered
     * @apiParam (Data) {string=completed,to_revise,null} [status=null] The meta status
     * @apiParam (Data) {int} user_id The meta user id
     * @apiParam (Data) {int} country_id The meta country_id
     * @apiParam (Data) {int} class_id_id The meta class_id_id
     * @apiParam (Data) {int} discipline_id The meta discipline_id
     * @apiParam (Data) {int} chapter_id The meta chapter_id
     * @apiParam (Data) {int} notion_id The meta notion_id
     * @apiParam (Data) {int} activity_id=null The meta activity_id
     * @apiUse         Http400InvalidRequest
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     * @apiUse         Http422ModelSave
     * @apiUse         Http422ModelValidation
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function mark(Request $request, int $id): JsonResponse
    {
        $user = $this->getUserByRequestOrUnauthorized($request);

        $metaData = array_merge(
            ["user_id" => $user->id, "module_id" => $id],
            $request->only(
                ["country_id", "class_id", "discipline_id", "notion_id", "chapter_id", "activity_id"]
            )
        );

        /** @var Meta $meta */
        $meta = Meta::firstOrNew($metaData);

        $meta->favorite = $request->input("favorite") ?? $meta->favorite ?? false;
        $meta->to_revise = $request->input("to_revise") ?? $meta->to_revise ?? false;
        $meta->completed = $request->input("completed") ?? $meta->completed ?? false;

        $meta->save();

        return response()->json(Module::find($id));
    }
}
