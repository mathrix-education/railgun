<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use function array_merge;
use function collect;

/**
 * Serve routes starting with /products.
 */
class ProductsController extends BaseController
{
    /**
     * PATCH /products/{packId}/items
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int     $packId  The pack id.
     *
     * @return PaginationJsonResponse The pack items list.
     *
     * @throws Http400BadRequestException
     */
    public function patchItems(Request $request, int $packId): PaginationJsonResponse
    {
        // TODO: Add validator

        /** @var Product $pack */
        $pack = Product::query()
            ->where('id', '=', $packId)
            ->where('type', '=', 'pack')
            ->firstOrFail();

        $itemsSync = collect($request->all())
            ->mapWithKeys(static function (int $itemId, int $order) {
                return [$itemId => ['order' => $order]];
            });

        $pack->items()
            ->sync($itemsSync);

        return new PaginationJsonResponse($pack->items());
    }

    /**
     * GET /products/{type}
     * List products of a given type.
     *
     * @param string $type The products type (pack|training|section|lesson).
     *
     * @return PaginationJsonResponse The products list.
     *
     * @throws Http400BadRequestException
     */
    public function paginateByType(string $type): PaginationJsonResponse
    {
        $query = Product::query()
            ->where('type', '=', $type);

        return new PaginationJsonResponse($query);
    }

    /**
     * GET /products/buyable/{type}
     * List buyable products of a given type.
     *
     * @param string $type The products type (pack|training|section|lesson).
     *
     * @return PaginationJsonResponse The buyable products list.
     *
     * @throws Http400BadRequestException
     */
    public function buyable(string $type): PaginationJsonResponse
    {
        $query = Product::with('children')
            ->where('buyable', '=', true);

        if ($type !== 'all') {
            $query = $query->where('type', '=', $type)
                ->where('visible', '=', true);
        }

        return new PaginationJsonResponse($query);
    }

    /**
     * GET /products/{productId}/recursive
     * Get a product with its children, recursively.
     *
     * @param int $productId The product id.
     *
     * @return SuccessJsonResponse The product and items children.
     */
    public function recursive(int $productId): SuccessJsonResponse
    {
        $maxDepth = 3;
        $with     = ['children'];

        for ($i = 1; $i <= $maxDepth; $i++) {
            $with[] = $with[$i - 1] . '.children';
        }

        foreach ($with as $relation) {
            $with[] = "items.$relation";
        }

        $withModules = [];
        foreach ($with as $relation) {
            $withModules[] = $relation . '.module';
        }

        $with    = array_merge($with, $withModules);
        $product = Product::with($with)
            ->findOrFail($productId);

        return new SuccessJsonResponse($product);
    }
}
