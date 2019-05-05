<?php

namespace App\Controllers;

use App\Models\Comment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class CommentsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class CommentsController extends BaseController
{
    /**
     * POST /comments
     * Post a new comment as logged user.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function post(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->merge([
            "user_id" => $user->id,
        ]);

        return parent::post($request);
    }


    /**
     * Get the models which satisfy a given relation.
     *
     * @param int $moduleId
     * @param int $page the query page
     * @param int $perPage the query per page
     *
     * @return PaginationJsonResponse
     */
    public function byModule(int $moduleId, int $page = 0, int $perPage = 100): PaginationJsonResponse
    {
        /** @var Module $module */
        $module = Module::query()->findOrFail($moduleId);

        $query = $module->comments()
            ->whereNull("parent_id")
            ->with(["children"]);

        return new PaginationJsonResponse($query, $page, $perPage);
    }


    /**
     * GET /comments/latest/{page}/{perPage}
     * Get the latest comments, ordered by desc created_at field.
     *
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of comments per page.
     *
     * @return JsonResponse
     */
    public function latest(int $page = 0, int $perPage = 100): JsonResponse
    {
        $query = Comment::with([
            "user:id,username,firstname,level,grade_id",
            "children",
            "children.user:id,username,firstname,level,grade_id"
        ])
            ->orderBy("created_at", "desc");

        return new PaginationJsonResponse($query, $page, $perPage);
    }
}
