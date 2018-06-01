<?php
namespace App\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DisciplineController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class DiscountController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /discounts/{?page}/{?perPage} Index
     * @apiGroup Discounts
     * @apiName DiscountsIndex
     * @apiDescription Retrieve discounts
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse DiscountArray
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /discounts/{id} Get
     * @apiGroup Discounts
     * @apiName DiscountGet
     * @apiDescription Get a discount by id
     * @apiPermission public
     * @apiParam {int} id The discount id
     * @apiUse DiscountModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {post} /discounts Add
     * @apiGroup Discounts
     * @apiName DiscountAdd
     * @apiDescription Add a discount
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse DiscountRequest
     * @apiUse DiscountModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */

    /**
     * @apiVersion 4.0.0
     * @api {patch} /discounts/{id} Edit
     * @apiGroup Discounts
     * @apiName DiscountEdit
     * @apiDescription Edit a discount
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The discount id
     * @apiUse DiscountRequest
     * @apiUse DiscountModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 4.0.0
     * @api {delete} /discounts/{id} Delete
     * @apiGroup Discounts
     * @apiName DiscountDelete
     * @apiDescription Delete a discount
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The discount id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.3
     * @api {post} /discounts/check Check
     * @apiGroup Discounts
     * @apiName DiscountCheck
     * @apiDescription Check if a discount is valid a discount
     * @apiPermission public
     * @apiUse Bearer
     * @apiParam {string} code The discount code
     * @apiUse DiscountModel
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function check(Request $request)
    {
        $discount = Discount::whereCode($request->input("code", "default"))->firstOrFail();

        return new JsonResponse($discount);
    }
}
