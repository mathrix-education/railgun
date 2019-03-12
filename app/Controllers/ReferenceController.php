<?php

namespace App\Controllers;

use App\Models\Reference;
use App\Responses\PaginationJsonResponse;
use Illuminate\Support\Str;

/**
 * Class ReferenceController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class ReferenceController extends BaseController
{
    /**
     * Query references by keys, comma-separated.
     * Example: test,lol%,%foo
     *
     * @param string $query The query string.
     * @param int $page The current page.
     * @param int $perPage The number of references per page.
     *
     * @return PaginationJsonResponse
     */
    public function query(string $query, int $page = 1, int $perPage = 100)
    {
        $queries = explode(",", $query);
        $builder = Reference::query();

        foreach ($queries as $q) {
            $operator = "=";

            if (Str::contains($query, "%")) {
                $operator = "LIKE";
            }

            $builder->orWhere("key", $operator, $q);
        }

        return new PaginationJsonResponse($builder, $page, $perPage);
    }
}
