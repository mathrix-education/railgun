<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Serve routes starting with /comments.
 */
class CommentsController extends BaseController
{
    /**
     * POST /comments
     * Post a new comment as logged user.
     *
     * @param Request $request The Illuminate HTTP.
     *
     * @return SuccessJsonResponse The created comment.
     */
    public function post(Request $request) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->merge([
            'user_id' => $user->id,
        ]);

        return parent::standardPost($request);
    }

    /**
     * GET /comments/latest
     * Get the latest comments, ordered by desc created_at field.
     *
     * @throws Http400BadRequestException
     */
    public function latest() : SuccessJsonResponse
    {
        $query = Comment::with([
            'user:id,username,firstname,grade_id',
            'children',
            'children.user:id,username,firstname,grade_id',
        ])
                        ->orderBy('created_at', 'desc');

        return new PaginationJsonResponse($query);
    }
}
