<?php
namespace App\Controllers\Braintree;

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
 * Class TransactionsController
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Controllers\Braintree
 * @since
 * @copyright Mathrix Education SA
 */
class TransactionsController extends BraintreeController
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
        } else {
            throw new Http400InvalidRequestException([
                "errors" => $saleResult->errors
            ]);
        }

        return $saleResult;
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

        return new JsonResponse($saleResult->jsonSerialize());
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
        $user = $request->user();

        $saleResult = $this->saleTo($request, $user);

        return new JsonResponse($saleResult->jsonSerialize());
    }


    /**
     * Find a transaction by its id
     *
     * @param Request $request
     *
     * @param $transactionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function find(Request $request, string $transactionId)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        return new JsonResponse(
            \Braintree_Transaction::find($transactionId)->jsonSerialize()
        );
    }


    /**
     * Refund a transaction by its id
     *
     * @param Request $request
     * @param string $transactionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function refund(Request $request, string $transactionId)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        return new JsonResponse(
            \Braintree_Transaction::refund($transactionId)->jsonSerialize()
        );
    }
}
