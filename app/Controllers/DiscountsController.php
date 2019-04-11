<?php

namespace App\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\ValidationException;

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
