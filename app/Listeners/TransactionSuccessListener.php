<?php
namespace App\Listeners;

use App\Events\TransactionSuccessEvent;
use App\Models\User;
use App\Notifications\TransactionSuccessNotification;

/**
 * Class TransactionSuccessListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Listeners
 */
class TransactionSuccessListener extends BaseListener
{
    /**
     * @param TransactionSuccessEvent $event
     */
    public function handle(TransactionSuccessEvent $event)
    {
        /** @var \Braintree\Transaction $transaction */
        $transaction = $event->transaction;
        /** @var \Braintree\Transaction\CustomerDetails $customerDetails */
        $customerDetails = $transaction->customerDetails;
        /** @var User $user */
        $user = User::where("braintree_id", "=", $customerDetails->id)
            ->firstOrFail();

        $user->notify(new TransactionSuccessNotification($user, $transaction));
    }
}
