<?php
namespace App\Responses;

use Illuminate\Database\Eloquent\Builder;
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
    public function __construct(Builder $query, int $page, int $perPage, $status = 200, $headers = [], $options = 0)
    {
        $total = $query->count();
        $models = $query->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        $data = [
            "page" => $page,
            "per_page" => $perPage,
            "total" => $total,
            "data" => $models,
        ];

        parent::__construct($data, $status, $headers, $options);
    }
}
