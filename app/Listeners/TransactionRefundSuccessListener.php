<?php

namespace App\Listeners;

use App\Events\TransactionRefundSuccessEvent;
use App\Models\User;
use App\Notifications\TransactionRefundSuccessNotification;
use Braintree\Transaction;

/**
 * Class TransactionRefundSuccessListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Listeners
 */
class TransactionRefundSuccessListener extends BaseListener
{
    /**
     * @param TransactionRefundSuccessEvent $event
     */
    public function handle(TransactionRefundSuccessEvent $event)
    {
        /** @var Transaction $transaction */
        $transaction = $event->transaction;
        /** @var \Braintree\Transaction\CustomerDetails $customerDetails */
        $customerDetails = $transaction->customerDetails;
        /** @var User $user */
        $user = User::where("braintree_id", "=", $customerDetails->id)
            ->firstOrFail();

        $user->notify(new TransactionRefundSuccessNotification($user, $transaction));
    }
}
