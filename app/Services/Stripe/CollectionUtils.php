<?php


namespace App\Services\Stripe;


use Illuminate\Support\Arr;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Stripe\ApiOperations\All;
use Stripe\Collection;
use Stripe\Error\Api as StripeAPIException;

class CollectionUtils
{
    /**
     * @param All|string $class The class to collect.
     * @param array $params The $class::$method() $parameters.
     * @param int|null $page The page (automatically derived form the querystring if not provided)
     * @param int|null $perPage The number of objects per page (automatically derived form the querystring if not
     *     provided)
     *
     * @return Collection
     * @throws StripeAPIException
     */
    public static function paginate($class, $params = [], ?int $page = null, ?int $perPage = null)
    {
        if ($page === null || $perPage === null) {
            [$page, $perPage] = PaginationJsonResponse::getPagination();
        }

        if ($page === 0) {
            // Page is 0, we can directly retrieve the collection without iterating
            $collection = $class::all(array_replace($params, [
                "limit" => $perPage
            ]));
        } else {
            $offset = $page * $perPage;
            $last = null;

            while ($offset > 0) {
                $params = array_replace($params, [
                    "limit" => min($offset, 100),
                    "starting_after" => $last
                ]);
                $temp = $class::all($params);
                $last = Arr::last($temp->data);
                $offset -= 100;

                if (!$temp->has_more) {
                    break;
                }
            }

            // Actual collection retrieval
            $params = array_replace($params, [
                "limit" => $perPage,
                "starting_after" => $last
            ]);
            $collection = $class::all($params);
        }

        return $collection;
    }
}
