<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Braintree\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductsController
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Controllers
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class ProductController extends BaseController
{
    /**
     * Base function for selling products
     *
     * @param Request $request
     * @param User $customer
     *
     * @return \Braintree\Result\Error|\Braintree\Result\Successful
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    private function saleTo(Request $request, User $customer)
    {
        $this->validate($request, [
            "cart" => "required",
            "cart.*" => "exists:products,id"
        ]);

        $paymentMethodNonce = $request->input("payment_method_nonce", false);

        if ($customer->isBraintreeCustomer()) {
            $customer->createBraintreeCustomer($paymentMethodNonce);
        }

        $cart = new Cart();
        $request = $cart->setCustomer($customer)
            ->setDiscount($request->input("discount"))
            ->setProducts($request->input("cart"))
            ->getBraintreeSaleRequest();

        $saleResult = \Braintree_Transaction::sale($request);

        if ($saleResult->success) {
            /** @var Product $product */
            foreach ($cart->getProducts() as $product) {
                $purchase = new Purchase([
                    "user_id" => $customer->id,
                    "product_id" => $product->id
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
        } else {
            $errors = [];

            /** @var \Braintree_Error_Validation $error */
            foreach ($saleResult->errors->deepAll() as $error) {
                $errors[$error->attribute] = $error->message;
            }

            throw new Http400InvalidRequestException("Sale failed", 400, null, [
                "errors" => $errors
            ]);
        }

        return $saleResult;
    }


    /**
     * Buy products with Braintree
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    public function buy(Request $request): JsonResponse
    {
        /** @var User $user */
        $customer = $request->user();

        $saleResult = $this->saleTo($request, $customer);

        return new JsonResponse($saleResult->transaction->jsonSerialize());
    }


    /**
     * Sale products to an user
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    public function sale(Request $request)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        /** @var User $customer */
        $customer = User::findOrFail($request->input("user_id"));

        $saleResult = $this->saleTo($request, $customer);

        return new JsonResponse($saleResult->transaction->jsonSerialize());
    }
}
