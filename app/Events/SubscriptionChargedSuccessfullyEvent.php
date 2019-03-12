<?php

namespace App\Events;

use Braintree\Subscription;
use Braintree\Transaction;

/**
 * Class SubscriptionChargedSuccessfullyEvent.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   App\Events
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
