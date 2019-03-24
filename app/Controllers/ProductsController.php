<?php

namespace App\Controllers;

use App\Events\TransactionSuccessEvent;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Braintree\Pipelines\CartPipeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
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
     * Base function for selling products.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param User $customer The customer.
     *
     * @return \Braintree\Result\Successful
     * @throws Http400BadRequestException
     * @throws ValidationException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    private function sellTo(Request $request, User $customer)
    {
        $this->validate($request, [
            "cart" => "required",
            "cart.*" => "exists:products,id",
        ]);

        $paymentMethodNonce = $request->input("paymentMethodNonce", false);

        if ($customer->isBraintreeCustomer()) {
            $customer->createBraintreeCustomer($paymentMethodNonce);
        }

        $cart = new CartPipeline();
        $request = $cart->setCustomer($customer)
            ->setDiscount($request->input("discount"))
            ->setProducts($request->input("cart"))
            ->getBraintreeSaleRequest();

        $saleResult = \Braintree\Transaction::sale($request);

        if ($saleResult->success) {
            /** @var Product $product */
            foreach ($cart->getProducts() as $product) {
                $purchase = new Purchase([
                    "user_id" => $customer->id,
                    "product_id" => $product->id,
                ]);
                $purchase->save();
            }

            if ($cart->getDiscount() !== null) {
                $discount = $cart->getDiscount();
                if ($discount->counter !== null && $discount->counter !== -1) {
                    $discount->counter--;
                    $discount->save();
                }
            }

            event(new TransactionSuccessEvent($saleResult->transaction));
        } else {
            $customer->record("braintree", "sale", "error", $saleResult->jsonSerialize());

            throw new Http400BadRequestException($saleResult->jsonSerialize(), "Sale failed");
        }

        return $saleResult;
    }


    /**
     * GET /products/buyable/{type}/{page}/{perPage}
     * Get buyable products.
     *
     * @param null $type all|training|lesson The product types.
     * @param int $page The page (starts at 0).
     * @param int $perPage The number of product per page.
     *
     * @return PaginationJsonResponse
     */
    public function buyable($type = null, $page = 0, $perPage = 100): PaginationJsonResponse
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
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function buy(Request $request): JsonResponse
    {
        /** @var User $user */
        $customer = $request->user();

        $saleResult = $this->sellTo($request, $customer);

        return new JsonResponse($saleResult->transaction->jsonSerialize());
    }


    /**
     * POST /products/sell
     * Sell products to an user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function sell(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        /** @var User $customer */
        $customer = User::query()->findOrFail($request->input("user_id"));

        $saleResult = $this->sellTo($request, $customer);

        return new JsonResponse($saleResult->transaction->jsonSerialize());
    }
}
