<?php

namespace App\Listeners;

use App\Events\TransactionSuccessEvent;
use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Braintree\Transaction;
use Braintree\Transaction\CustomerDetails;

/**
 * Class TransactionSuccessListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class TransactionSuccessListener extends BaseListener
{
    /**
     * @param TransactionSuccessEvent $event
     */
    public function handle(TransactionSuccessEvent $event)
    {
        /** @var Transaction $transaction */
        $transaction = $event->transaction;
        /** @var CustomerDetails $customerDetails */
        $customerDetails = $transaction->customerDetails;
        /** @var User $user */
        $user = User::query()
            ->where("braintree_id", "=", $customerDetails->id)
            ->firstOrFail();

        $user->notify(new TransactionSuccessNotification($user, $transaction));
    }
}
