<?php

namespace App\Controllers;

use App\Models\Record;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class RecordsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class RecordsController extends BaseController
{
    /**
     * GET /records/by-user/{userId}
     *
     * @param int $userId The user id.
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of records per page.
     *
     * @return JsonResponse
     */
    public function byUser(int $userId, int $page, int $perPage): JsonResponse
    {
        $recordsQuery = Record::query()->where("user_id", "=", $userId);

        return new PaginationJsonResponse($recordsQuery, $page, $perPage);
    }
}
