<?php

namespace App\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Exceptions\ValidationException;
use Mathrix\Lumen\Responses\PaginationJsonResponse;

/**
 * Class DiscountsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.0.3
 */
class DiscountsController extends BaseController
{
    /**
     * GET /discounts/{discountId}/products/{page}/{perPage}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $discountId The discount id.
     * @param int $page The results page.
     * @param int $perPage The number of products per page.
     *
     * @return PaginationJsonResponse
     * @throws Http401UnauthorizedException
     */
    public function products(Request $request, int $discountId, int $page, int $perPage): PaginationJsonResponse
    {
        return $this->relation($request, "products", $discountId, $page, $perPage);
    }


    /**
     * PATCH /discount/{discountId}/products
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $discountId The discount id.
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function patchProducts(Request $request, int $discountId): JsonResponse
    {
        $this->validate($request, [
            "*" => "distinct|integer|exists:products,id"
        ]);

        /** @var Discount $discount */
        $discount = Discount::query()->findOrFail($discountId);
        $discount->products()->sync($request->all());

        return new PaginationJsonResponse($discount->products(), 1, 1000);
    }


    /**
     * GET /discounts/code/{discountCode}
     * Check if a discount is valid.
     *
     * @param string $code The discount code.
     *
     * @return JsonResponse
     */
    public function getByCode(string $code)
    {
        $discount = Discount::query()
            ->where("code", "=", $code)
            ->firstOrFail();

        return new JsonResponse($discount);
    }
}

