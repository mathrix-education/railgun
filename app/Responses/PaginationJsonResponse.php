<?php
namespace App\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class PaginationJsonResponse.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class PaginationJsonResponse extends JsonResponse
{
    public function __construct($data, int $page, int $perPage, int $total, $status = 200, $headers = [], $options = 0)
    {
        $data = [
            "page" => $page,
            "per_page" => $perPage,
            "total" => $total,
            "data" => $data,
        ];

        parent::__construct($data, $status, $headers, $options);
    }
}
