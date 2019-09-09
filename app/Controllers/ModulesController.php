<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Mark;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Exceptions\Models\Models\ModelNotFoundException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

class ModulesController extends BaseController
{
    /**
     * GET /modules/{moduleId}/comments/{page}/{perPage}
     *
     * @param int $moduleId The module id.
     *
     * @throws Http400BadRequestException
     */
    public function getComments(int $moduleId) : PaginationJsonResponse
    {
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);
        $query  = $module->comments()
            ->whereNull('parent_id')
            ->with(['children']);

        return new PaginationJsonResponse($query);
    }

    /**
     * GET /modules/{moduleId}/chapter/recursive
     *
     * @param Request            $request            The Illuminate HTTP request.
     * @param ChaptersController $chaptersController The Chapters Controller
     * @param int                $moduleId           The module id.
     *
     * @throws ModelNotFoundException
     */
    public function chapterRecursive(Request $request, ChaptersController $chaptersController, int $moduleId) : SuccessJsonResponse
    {
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);

        if (! isset($module->activity->notion->chapter_id)) {
            throw new ModelNotFoundException(['module_id' => $module->id]);
        }

        return $chaptersController->recursive($request, $module->activity->notion->chapter_id);
    }

    /**
     * POST /modules/mark
     * Mark a module.
     *
     * @param Request $request The Illuminate HTTP request.
     */
    public function mark(Request $request, int $moduleId) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);
        $status = $request->json('status');

        $mark         = Mark::retrieve($module, $user);
        $mark->status = $status;
        $mark->save();

        return new SuccessJsonResponse($module->refresh());
    }
}
