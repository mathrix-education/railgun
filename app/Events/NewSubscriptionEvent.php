<?php

namespace App\Events;

use App\Models\User;
use Braintree\Subscription;
use Braintree\Transaction;

/**
 * Class NewSubscriptionEvent.
 * Fired when a user successfully subscribed to Mathrix Premium.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class NewSubscriptionEvent extends BaseEvent
{
    /** @var User $user */
    public $user;
    /** @var \Braintree_Subscription $subscription */
    public $subscription;
    /** @var Transaction $initialTransaction */
    public $initialTransaction;


    /**
     * NewSubscriptionEvent constructor.
     *
     * @param User $user
     * @param Subscription $subscription
     * @param Transaction $initialTransaction
     */
    public function __construct(User $user, Subscription $subscription, Transaction $initialTransaction = null)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->initialTransaction = $initialTransaction;
    }
}
