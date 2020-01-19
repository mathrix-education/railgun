<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\DataResponse;

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
     * @return DataResponse The created comment.
     */
    public function create(Request $request): DataResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->merge([
            'user_id' => $user->id,
        ]);

        return $this->defaultCreate($request);
    }
}
