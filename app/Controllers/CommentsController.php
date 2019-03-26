<?php

namespace App\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
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
     * GET /comments/latest/{page}/{perPage}
     * Get the latest comments, ordered by desc created_at field.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of comments per page.
     *
     * @return JsonResponse
     */
    public function latest(Request $request, $page = 0, $perPage = 100): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->canOrFail("latest", Comment::class);

        $query = Comment::query()
            ->orderBy("created_at", "desc");
        $response = new PaginationJsonResponse($query, $page, $perPage);

        return $response;
    }
}
