<?php
namespace App\Events;

use App\Models\User;

/**
 * Class NewSubscriptionEvent
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Events
 * @since
 * @copyright Mathrix Education SA
 */
class NewSubscriptionEvent extends BaseEvent
{
    /** @var User $user */
    public $user;
    /** @var \Braintree_Subscription $subscription */
    public $subscription;


    /**
     * NewSubscriptionEvent constructor.
     *
     * @param User $user
     * @param \Braintree_Subscription $subscription
     */
    public function __construct(User $user, \Braintree_Subscription $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
    }
}
