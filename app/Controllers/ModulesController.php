<?php

namespace App\Controllers;

use App\Models\Mark;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Exceptions\Models\Models\ModelNotFoundException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class ModulesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class ModulesController extends BaseController
{
    /**
     * @param int $page
     * @param int $perPage
     *
     * @return PaginationJsonResponse
     *
     * @deprecated temporary method, to help to migrate to silicate
     */
    public function courses(int $page, int $perPage)
    {
        $courses = Module::query()
            ->where("type", "=", "course");

        return new PaginationJsonResponse($courses, $page, $perPage);
    }


    /**
     * GET /modules/{moduleId}/comments/{page}/{perPage}
     *
     * @param int $moduleId The module id.
     * @param int $page The results page (starts at 0).
     * @param int $perPage The number of comments per page.
     *
     * @return PaginationJsonResponse
     */
    public function comments(int $moduleId, int $page, int $perPage): PaginationJsonResponse
    {
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);
        $query = $module->comments()
            ->whereNull("parent_id")
            ->with(["children"]);

        return new PaginationJsonResponse($query, $page, $perPage);
    }


    /**
     * GET /modules/{moduleId}/reports/{page}/{perPage}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $moduleId The module id.
     * @param int $page The results page (starts at 0).
     * @param int $perPage The number of reports per page.
     *
     * @return PaginationJsonResponse
     * @throws Http401UnauthorizedException
     */
    public function reports(Request $request, int $moduleId, int $page, int $perPage): PaginationJsonResponse
    {
        return $this->relation($request, "reports", $moduleId, $page, $perPage);
    }


    /**
     * GET /modules/{moduleId}/chapter/recursive
     *
     * @param Request $request The Illuminate HTTP request.
     * @param ChaptersController $chaptersController The Chapters Controller
     * @param int $moduleId The module id.
     *
     * @return JsonResponse
     * @throws ModelNotFoundException
     */
    public function chapterRecursive(Request $request, ChaptersController $chaptersController, int $moduleId): JsonResponse
    {
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);

        if (!isset($module->activity->notion->chapter_id)) {
            throw new ModelNotFoundException(["module_id" => $module->id]);
        }

        return $chaptersController->recursive($request, $module->activity->notion->chapter_id);
    }


    /**
     * POST /modules/mark
     * Mark a module.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $moduleId
     *
     * @return JsonResponse
     */
    public function mark(Request $request, int $moduleId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);
        $status = $request->json("status");

        $mark = Mark::retrieve($module, $user);
        $mark->status = $status;
        $mark->save();

        return new JsonResponse($module->refresh());
    }
}
