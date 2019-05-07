<?php

namespace App\Controllers;

use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class NotionsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class NotionsController extends BaseController
{
    /**
     * GET /notions/{notionId}/activities/{page}/{perPage}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $notionId The notion id.
     * @param int $page The results page.
     * @param int $perPage The number of notions per page.
     *
     * @return PaginationJsonResponse
     * @throws Http401UnauthorizedException
     */
    public function activities(Request $request, int $notionId, int $page, int $perPage): PaginationJsonResponse
    {
        return $this->relation($request, "activities", $notionId, $page, $perPage);
    }
}
