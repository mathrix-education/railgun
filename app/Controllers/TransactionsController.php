<?php

namespace App\Controllers;

use App\Events\TransactionRefundSuccessEvent;
use App\Models\User;
use App\Services\Braintree\Utils\Formatter;
use Braintree\Subscription;
use Braintree\Transaction;
use Braintree\TransactionSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;

/**
 * Class TransactionsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class TransactionsController extends BaseController
{
    /**
     * GET /transactions/{transactionId}
     * Find a transaction by its id.
     *
     * @param string $transactionId The Braintree transaction id.
     *
     * @return JsonResponse
     */
    public function find(string $transactionId)
    {
        $transaction = Transaction::find($transactionId);

        return new JsonResponse(
            Formatter::cast($transaction->jsonSerialize())
        );
    }


    /**
     * POST /transactions/{transactionId}/void
     * Attempt to void a transaction by its id.
     *
     * @param string $transactionId The Braintree transaction id.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     */
    public function void(string $transactionId)
    {
        $voidableStatuses = [
            "authorizing",
            "authorized",
            "submitted_for_settlement",
            "settlement_pending",
        ];
        $transaction = Transaction::find($transactionId);

        if (in_array($transaction->status, $voidableStatuses, true)) {
            $result = Transaction::void($transactionId);

            if ($result->success) {
                event(new TransactionRefundSuccessEvent($result->transaction));

                return new JsonResponse(
                    Formatter::cast($result->transaction->jsonSerialize())
                );
            }

            throw new Http400BadRequestException(
                $result->transaction->jsonSerialize(),
                "Unable to void transaction `id` $transactionId"
            );
        }
        throw new Http400BadRequestException(
            $transaction->jsonSerialize(),
            "Too late to void transaction `id` $transactionId; refund instead"
        );
    }


    /**
     * POST /transactions/{transactionId}/refund
     * Refund a transaction by its id.
     *
     * @param string $transactionId The Braintree transaction id.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     */
    public function refund(string $transactionId)
    {
        // Check if the transaction can be voided
        $voidableStatuses = [
            "authorizing",
            "authorized",
            "submitted_for_settlement",
            "settlement_pending",
        ];
        $transaction = Transaction::find($transactionId);
        if (in_array($transaction->status, $voidableStatuses, true)) {
            return $this->void($transactionId);
        }

        $result = Transaction::refund($transactionId);

        if ($result->success) {
            event(new TransactionRefundSuccessEvent($result->transaction));

            return new JsonResponse(
                Formatter::cast($result->transaction->jsonSerialize())
            );
        }

        throw new Http400BadRequestException($result->transaction->jsonSerialize(), $result->message);
    }


    /**
     * GET /transactions/by-subscription/{subscriptionId}
     * Get transactions linked with a subscription.
     *
     * @param string $subscriptionId The Braintree subscription id.
     *
     * @return JsonResponse
     */
    public function bySubscription(string $subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);
        $transactions = $subscription->jsonSerialize()["transactions"];

        return new JsonResponse(Formatter::cast($transactions));
    }


    /**
     * GET /transactions/by-user/{userId}
     * Get transactions linked with an user.
     *
     * @param string $userId The user id.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     */
    public function byUser(string $userId)
    {
        /** @var User $customer */
        $customer = User::query()->findOrFail($userId);
        $customer->requireBraintreeCustomer();
        $transactionResults = Transaction::search([
            TransactionSearch::customerId()
                ->is($customer->braintree_id),
        ]);
        $transactions = new Collection();

        foreach ($transactionResults as $transaction) {
            $transactions->add(Formatter::cast($transaction));
        }

        return new JsonResponse($transactions);
    }
}
