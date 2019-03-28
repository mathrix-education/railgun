<?php

namespace App\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;

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
     * POST /discounts/check
     * Check if a discount is valid.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function check(Request $request)
    {
        $this->validate($request, [
            "code" => "required"
        ]);
        $code = $request->input("code");

        $discount = Discount::query()
            ->where("code", "=", $code)
            ->firstOrFail();

        return new JsonResponse($discount);
    }
}
