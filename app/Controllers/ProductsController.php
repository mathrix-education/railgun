<?php

namespace App\Controllers;

use App\Events\TransactionSuccessEvent;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Braintree\Pipelines\CartPipeline;
use Braintree\Result\Successful;
use Braintree\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Exceptions\ValidationException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class ProductsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class ProductsController extends BaseController
{
    /**
     * GET /products/slug/{productSlug}
     *
     * @param string $productSlug The products slug.
     *
     * @return JsonResponse
     */
    public function getBySlug(string $productSlug)
    {
        $product = Product::query()
            ->where("slug", "=", $productSlug)
            ->firstOrFail();

        return new JsonResponse($product);
    }


    /**
     * GET /products/buyable/{type}/{page}/{perPage}
     * Get buyable products.
     *
     * @param string $type all|training|section|lesson The product types.
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of product per page.
     *
     * @return PaginationJsonResponse
     */
    public function buyable(int $page = 0, int $perPage = 100, $type = "all"): PaginationJsonResponse
    {
        $query = Product::with("children")
            ->whereNotNull("price");

        if ($type !== "all") {
            $query = $query->where("type", "=", $type);
        }

        return new PaginationJsonResponse($query, $page, $perPage);
    }


    /**
     * GET /products/{productId}/recursive
     * Get a product with its children, recursively.
     *
     * @param int $productId The product id.
     *
     * @return JsonResponse
     */
    public function recursive(int $productId): JsonResponse
    {
        $maxDepth = 3;
        $with = ["children"];

        for ($i = 1; $i <= $maxDepth; $i++) {
            $with[] = $with[$i - 1] . ".children";
        }

        $withModules = [];
        foreach ($with as $relation) {
            $withModules[] = $relation . ".module";
        }

        $with = array_merge($with, $withModules);
        $product = Product::with($with)->findOrFail($productId);

        return new JsonResponse($product);
    }


    /**
     * POST /products/buy
     * Buy products with Braintree.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws ValidationException
     */
    public function buy(Request $request): JsonResponse
    {
        return (new CartPipeline($request))->process();
    }
}
