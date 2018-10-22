<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Meta;
use App\Models\Module;
use App\Models\Notion;
use App\Models\User;
use App\Utils\RouterFront;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     * @param Request $request The request
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
        $module = Module::where("id", $key)
            ->firstOrFail();

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
     * @param Request $request The request
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

    /**
     * Get modules routes
     *
     * @param int $moduleId
     *
     * @return JsonResponse
     */
    public function routes(int $moduleId): JsonResponse
    {
        $module = Module::findOrFail($moduleId);

        $routes = [];

        foreach ($module->activities as $activity) {
            foreach ($activity->notions as $notion) {
                foreach ($notion->chapters as $chapter) {
                    $class = $chapter->class_;
                    $discipline = $chapter->discipline;
                    $country = $class->country;

                    $url = RouterFront::getModuleUrl($country, $class, $discipline, $chapter, $notion, $activity, $module);
                    $seoIndex = RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity, $module);

                    $routes[] = [
                        "url" => $url,
                        "seo_index" => $seoIndex
                    ];
                }
            }
        }

        $response = new JsonResponse($routes);

        return $response;
    }

    /**
     * Mark a content route a no-index.
     *
     * @param Request $request
     * @param int     $moduleId
     *
     * @return JsonResponse
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     * @throws Http400InvalidRequestException
     */
    public function markIndex(Request $request, int $moduleId)
    {
        $request->user()->canOrFail("markIndex", Module::class);

        $this->validate($request, [
            "country_id" => "required|exists:countries,id",
            "class_id" => "required|exists:classes,id",
            "discipline_id" => "required|exists:disciplines,id",
            "chapter_id" => "required|exists:chapters,id",
            "notion_id" => "required|exists:notions,id",
            "activity_id" => "required|exists:activities,id",
        ]);

        $country = Country::findOrFail($request->input("country_id"));
        $class = Class_::findOrFail($request->input("class_id"));
        $discipline = Discipline::findOrFail($request->input("discipline_id"));
        $chapter = Chapter::findOrFail($request->input("chapter_id"));
        $notion = Notion::findOrFail($request->input("notion_id"));
        $activity = Activity::findOrFail($request->input("activity_id"));
        $module = Module::findOrFail($moduleId);

        $cancel = $request->input("cancel", false);
        $seoIndex = RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity, $module);

        if (!$cancel && $seoIndex) {
            throw new Http400InvalidRequestException("Already marked as seo_index = true");
        } elseif ($cancel && !$seoIndex) {
            $existingMeta = Meta::where([
                ["country_id", "=", $country->id],
                ["class_id", "=", $class->id],
                ["discipline_id", "=", $discipline->id],
                ["chapter_id", "=", $chapter->id],
                ["notion_id", "=", $notion->id],
                ["activity_id", "=", $activity->id],
                ["module_id", "=", $module->id],
                ["seo_index", "=", true]
            ])
                ->whereNull("user_id")
                ->firstOrFail();
            $existingMeta->delete();
        } else {
            $meta = new Meta([
                "favorite" => false,
                "to_revise" => false,
                "completed" => false,
                "seo_index" => true,
                "user_id" => null,
                "country_id" => $country->id,
                "class_id" => $class->id,
                "discipline_id" => $discipline->id,
                "chapter_id" => $chapter->id,
                "notion_id" => $notion->id,
                "activity_id" => $activity->id,
                "module_id" => $module->id
            ]);
            $meta->save();
        }

        $module = Module::findOrFail($moduleId);
        $response = new JsonResponse(array_merge(
            $module->toArray(),
            ["seo_index" => RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity, $module)]
        ));

        return $response;
    }
}
