<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;

/**
 * Serve routes starting with /posts.
 */
class PostsController extends BaseController
{
    /**
     * GET /posts
     * Get all the posts, in descending creation date.
     *
     * @throws Http400BadRequestException
     */
    public function latest(): PaginationJsonResponse
    {
        $query = Post::with('module')
            ->orderByDesc('created_at');

        return new PaginationJsonResponse($query);
    }
}
