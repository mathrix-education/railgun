<?php
namespace App\Events;

use Braintree\Subscription;
use Braintree\Transaction;

/**
 * Class SubscriptionChargedSuccessfullyEvent.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.1.3
 * @copyright Mathrix Education SA
 */
class SubscriptionChargedSuccessfullyEvent
{
    /** @var Subscription $subscription */
    public $subscription;
    /** @var Transaction $transaction */
    public $transaction;

    /**
     * TransactionSuccessEvent constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
        $this->transaction = $subscription->transactions[0];
    }
}
