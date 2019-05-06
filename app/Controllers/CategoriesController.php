<?php

namespace App\Controllers;

use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class CategoriesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class CategoriesController extends BaseController
{
    /**
     * GET /categories/{categoryId}/posts/{page}/{perPage}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $categoryId The category id.
     * @param int $page The results page (starts at 0).
     * @param int $perPage The number of posts per page.
     *
     * @return PaginationJsonResponse
     * @throws Http401UnauthorizedException
     */
    public function posts(Request $request, int $categoryId, int $page, int $perPage): PaginationJsonResponse
    {
        return $this->relation($request, "posts", $categoryId, $page, $perPage);
    }
}
