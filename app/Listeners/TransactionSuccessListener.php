<?php

namespace App\Listeners;

use App\Events\TransactionSuccessEvent;
use App\Models\User;
use App\Notifications\TransactionSuccessNotification;
use Braintree\Transaction;
use Braintree\Transaction\CustomerDetails;
use Exception;
use Mathrix\Lumen\Bases\BaseListener;
use Raven_Client;

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
     * Handle TransactionSuccessEvent.
     *
     * @param TransactionSuccessEvent $event
     */
    public function handle(TransactionSuccessEvent $event)
    {
        try {
            /** @var Transaction $transaction */
            $transaction = $event->transaction;
            /** @var CustomerDetails $customerDetails */
            $customerDetails = $transaction->customerDetails;
            /** @var User $user */
            $user = User::query()
                ->where("braintree_id", "=", $customerDetails->id)
                ->firstOrFail();

            $user->notify(new TransactionSuccessNotification($user, $transaction));
        } catch (Exception $e) {
            if (app()->bound("sentry")) {
                /** @var Raven_Client $sentry */
                $sentry = app()->make("sentry");
                if (isset($transaction->customerDetails->id)) {
                    $user = User::query()
                        ->where("braintree_id", "=", $transaction->customerDetails->id)
                        ->first();

                    if ($user !== null) {
                        $sentry->user_context($user->toArray());
                    } else {
                        $sentry->user_context(["braintree_id" => $transaction->customerDetails->id]);
                    }
                }

                $sentry->captureException($e);
            }
        }
    }
}
