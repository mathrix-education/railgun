<?php

namespace App\Controllers;

use App\Events\TransactionRefundSuccessEvent;
use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Models\User;
use App\Services\Braintree\Formatter;
use Braintree_TransactionSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

/**
 * Class TransactionController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
 */
class TransactionController extends Controller
{
    /**
     * Find a transaction by its id.
     *
     * @param Request $request       The request
     * @param string  $transactionId The Braintree transaction id
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function find(Request $request, string $transactionId)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $transaction = \Braintree\Transaction::find($transactionId);

        return new JsonResponse(
            Formatter::cast($transaction->jsonSerialize())
        );
    }

    /**
     * Refund a transaction by its id.
     *
     * @param Request $request       The request
     * @param string  $transactionId The Braintree transaction id
     *
     * @throws Http401UnauthorizedException
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function refund(Request $request, string $transactionId)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        // Check if the transaction can be voided
        $voidableStatuses = [
            "authorizing",
            "authorized",
            "submitted_for_settlement",
            "settlement_pending",
        ];
        $transaction = \Braintree\Transaction::find($transactionId);
        if (\in_array($transaction->status, $voidableStatuses, true)) {
            return $this->void($request, $transactionId);
        }

        $result = \Braintree\Transaction::refund($transactionId);

        if ($result->success) {
            event(new TransactionRefundSuccessEvent($result->transaction));

            return new JsonResponse(
                Formatter::cast($result->transaction->jsonSerialize())
            );
        }
        throw new Http400InvalidRequestException($result->message);
    }

    /**
     * Attempt to void a transaction by its id.
     *
     * @param Request $request       The request
     * @param string  $transactionId The Braintree transaction id
     *
     * @throws Http401UnauthorizedException
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
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
            "settlement_pending",
        ];
        $transaction = \Braintree\Transaction::find($transactionId);

        if (\in_array($transaction->status, $voidableStatuses, true)) {
            $result = \Braintree\Transaction::void($transactionId);

            if ($result->success) {
                event(new TransactionRefundSuccessEvent($result->transaction));

                return new JsonResponse(
                    Formatter::cast($result->transaction->jsonSerialize())
                );
            }
            throw new Http400InvalidRequestException("Unable to void transaction `id` $transactionId");
        }
        throw new Http400InvalidRequestException("Too late to void transaction `id` $transactionId;"
            . " refund instead");
    }

    /**
     * Get transactions linked with a subscription.
     *
     * @param Request $request        The request
     * @param string  $subscriptionId The Braintree subscription id
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function bySubscription(Request $request, string $subscriptionId)
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        $subscription = \Braintree_Subscription::find($subscriptionId);
        $transactions = $subscription->jsonSerialize()["transactions"];

        return new JsonResponse(Formatter::cast($transactions));
    }

    /**
     * Get transactions linked with an user.
     *
     * @param Request $request The request
     * @param string  $userId  The user id
     *
     * @throws Http401UnauthorizedException
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
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
        $transactionResults = \Braintree\Transaction::search([
            Braintree_TransactionSearch::customerId()
                ->is($customer->braintree_id),
        ]);
        $transactions = new Collection();

        foreach ($transactionResults as $transaction) {
            $transactions->add(Formatter::cast($transaction));
        }

        return new JsonResponse($transactions);
    }
}
