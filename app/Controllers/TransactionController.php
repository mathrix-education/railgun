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
 * Class TransactionController
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Controllers\Braintree
 * @since
 * @copyright Mathrix Education SA
 */
class TransactionController extends BraintreeController
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
     * Attempt to void a transaction by its id
     *
     * @param Request $request
     * @param string $transactionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http400InvalidRequestException
     */
    public function void(Request $request, string $transactionId)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $transaction = \Braintree_Transaction::find($transactionId);

        if (in_array($transaction->status, ["authorized", "submitted_for_settlement", "settlement_pending"])) {
            $result = \Braintree_Transaction::void($transactionId);

            if ($result->success) {
                return new JsonResponse([
                    "status" => "success",
                    "message" => "Successfully voided transaction `id` $transactionId"
                ]);
            } else {
                throw new Http400InvalidRequestException("Unable to void transaction `id` $transactionId");
            }
        } else {
            throw new Http400InvalidRequestException("Too late to void transaction `id` $transactionId;"
                . " refund instead");
        }
    }


    /**
     * Refund a transaction by its id
     *
     * @param Request $request
     * @param string $transactionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http400InvalidRequestException
     */
    public function refund(Request $request, string $transactionId)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $result = \Braintree_Transaction::refund($transactionId);

        if ($result->success) {
            return new JsonResponse([
                "status" => "success",
                "message" => "Successfully refunded transaction `id` $transactionId"
            ]);
        } else {
            throw new Http400InvalidRequestException("Unable to refund transaction `id` $transactionId");
        }
    }


    /**
     * Get transactions linked with a subscription
     *
     * @param Request $request
     * @param string $subscriptionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function bySubscription(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $subscription = \Braintree_Subscription::find($subscriptionId);

        return new JsonResponse($subscription->jsonSerialize()["transactions"]);
    }
}
