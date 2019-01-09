<?php
namespace App\Events;

use App\Models\User;

/**
 * Class NewSubscriptionEvent.
 * Fired when a user successfully subscribed to Mathrix Premium.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Events
 */
class NewSubscriptionEvent extends BaseEvent
{
    /** @var User $user */
    public $user;
    /** @var \Braintree_Subscription $subscription */
    public $subscription;
    /** @var \Braintree\Transaction $initialTransaction */
    public $initialTransaction;

    /**
     * NewSubscriptionEvent constructor.
     *
     * @param User                    $user
     * @param \Braintree_Subscription $subscription
     * @param \Braintree\Transaction  $initialTransaction
     */
    public function __construct(User $user, \Braintree_Subscription $subscription, \Braintree\Transaction $initialTransaction = null)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->initialTransaction = $initialTransaction;
    }
}
