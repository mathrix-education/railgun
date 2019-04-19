<?php

namespace App\Controllers;

use App\Models\Mark;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;

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
