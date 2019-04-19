<?php

namespace App\Events;

use Braintree\Subscription;
use Braintree\Transaction;

/**
 * Class SubscriptionChargedSuccessfullyEvent.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.2.0
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
     * @param Subscription $subscription
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
        $this->transaction = $subscription->transactions[0];
    }
}
