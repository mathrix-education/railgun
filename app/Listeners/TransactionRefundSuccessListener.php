<?php

namespace App\Listeners;

use App\Events\TransactionRefundSuccessEvent;
use App\Models\User;
use App\Notifications\TransactionRefundSuccessNotification;
use Braintree\Transaction;
use Braintree\Transaction\CustomerDetails;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class TransactionRefundSuccessListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class TransactionRefundSuccessListener extends BaseListener
{
    /**
     * Handle TransactionRefundSuccessEvent.
     *
     * @param TransactionRefundSuccessEvent $event
     */
    public function handle(TransactionRefundSuccessEvent $event)
    {
        /** @var Transaction $transaction */
        $transaction = $event->transaction;

        /** @var CustomerDetails $customerDetails */
        $customerDetails = $transaction->customerDetails;

        /** @var User $user */
        $user = User::query()
            ->where("braintree_id", "=", $customerDetails->id)
            ->firstOrFail();

        $user->notify(new TransactionRefundSuccessNotification($user, $transaction));
    }
}
