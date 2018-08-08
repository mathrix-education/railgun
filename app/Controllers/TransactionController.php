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
use Braintree_TransactionSearch;
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

        $voidableStatuses = [
            "authorizing",
            "authorized",
            "submitted_for_settlement",
            "settlement_pending"
        ];
        $transaction = \Braintree_Transaction::find($transactionId);

        if (in_array($transaction->status, $voidableStatuses)) {
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


    /**
     * Get transactions linked with an user
     *
     * @param Request $request
     * @param string $userId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http400InvalidRequestException
     */
    public function byUser(Request $request, string $userId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        /** @var User $customer */
        $customer = User::findOrFail($userId);
        $customer->requireBraintreeCustomer();
        $transactions = \Braintree_Transaction::search([
            Braintree_TransactionSearch::customerId()->is($customer->braintree_id)
        ]);

        return new JsonResponse($transactions);
    }
}
