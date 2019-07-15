<?php

namespace App\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;

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
}

