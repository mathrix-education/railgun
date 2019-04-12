<?php

namespace App\Controllers\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Trait SearchTrait.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
trait SearchTrait
{
    /**
     * Generic search action.
     *
     * @param Request $request The request
     * @param int $page
     * @param int $perPage
     *
     * @return PaginationJsonResponse
     */
    public function search(Request $request, $page = 1, $perPage = 100): PaginationJsonResponse
    {
        $request->user()->canOrFail("search", $this->modelClass);
        $inputs = $request->except(["order", "having"]);

        foreach ($inputs as $key => $value) {
            // Remove the URL as input
            if (Str::contains($key, "/")) {
                unset($inputs[$key]);
            }
        }

        $order = array_merge([
            "column" => "id",
            "dir" => "asc",
        ], $request->input("order", []));
        $conditions = [];
        $having = [];

        $this->validate($request, [
            "order.dir" => "in:asc,desc"
        ]);

        // Convert request form data to valid eloquent conditions
        foreach ($inputs as $column => $value) {
            $operator = "=";
            if (mb_strstr($value, "%") !== false) {
                $operator = "LIKE";
            }
            $conditions[] = [$column, $operator, $value];
        }
        foreach ($request->input("having", []) as $relation => $relationConditions) {
            foreach ($relationConditions as $column => $value) {
                $operator = "=";
                if (mb_strstr($value, "%") !== false) {
                    $operator = "LIKE";
                }
                $having[$relation][] = [$column, $operator, $value];
            }
        }
        // Build query
        /** @var Builder $queryBuilder */
        $queryBuilder = $this->modelClass::query()
            ->where($conditions);

        foreach ($having as $relation => $havingConditions) {
            $queryBuilder = $queryBuilder->whereHas($relation, function (Builder $query) use ($havingConditions) {
                return $query->where($havingConditions);
            });
        }

        $queryBuilder = $queryBuilder->orderBy($order["column"], $order["dir"]);

        return new PaginationJsonResponse($queryBuilder, $page, $perPage);
    }
}
