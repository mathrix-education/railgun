<?php

namespace App\Controllers;

use App\Models\Post;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

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
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of posts per page.
     *
     * @return PaginationJsonResponse
     */
    public function latest(int $page = 0, int $perPage = 10): PaginationJsonResponse
    {
        $query = Post::with("module")
            ->orderBy("created_at", "desc");

        return new PaginationJsonResponse($query, $page, $perPage);
    }
}
