<?php
namespace App\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class PaginationJsonResponse.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Responses
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
