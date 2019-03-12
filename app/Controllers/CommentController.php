<?php

namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\Module;
use App\Models\Path;
use App\Models\User;
use App\Responses\PaginationJsonResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CommentController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class CommentController extends BaseController
{
    /**
     * Post a new comment.
     *
     * @param Request $request The request
     *
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("add", Comment::class);

        // Get path
        $pathData = $request->input("path");
        $path = Path::retrieve($pathData);

        /** @var BaseModel $model */
        $comment = new Comment($request->only([
            "content",
            "parent_id",
            "module_id"
        ]));
        $comment->user_id = $user->id;
        $comment->path_id = $path->id;
        $comment->save();

        return response()->json($comment);
    }


    /**
     * Get comments associated with a given model.
     *
     * @param Request $request The request
     * @param string $what
     * @param int $id
     * @param int $page
     * @param int $perPage
     *
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function by(Request $request, string $what, int $id, int $page = 1, int $perPage = 100): JsonResponse
    {
        $modelClass = $this->resolveModelClass($what);

        /** @var BaseModel|Module $model */
        $model = $modelClass::where("id", $id)
            ->firstOrFail();

        $query = $model->comments()
            ->whereNull("parent_id");

        $response = new PaginationJsonResponse($query, $page, $perPage);

        return $response;
    }


    /**
     * Get the latest comments, ordered by desc created_at field.
     *
     * @param Request $request The request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     */
    public function latest(Request $request, $page = 1, $perPage = 100): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->canOrFail("latest", Comment::class);

        $query = Comment::orderBy("created_at", "desc");
        $response = new PaginationJsonResponse($query, $page, $perPage);

        return $response;
    }
}
