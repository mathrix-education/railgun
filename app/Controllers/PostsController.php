<?php

namespace App\Controllers;

use App\Models\Post;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;

/**
 * Class PostsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class PostsController extends BaseController
{
    /**
     * GET /posts
     * Get all the posts, in descending creation date.
     *
     * @return PaginationJsonResponse
     * @throws Http400BadRequestException
     */
    public function latest(): PaginationJsonResponse
    {
        $query = Post::with("module")
            ->orderByDesc("created_at");

        return new PaginationJsonResponse($query);
    }
}
