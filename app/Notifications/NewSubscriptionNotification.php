<?php

namespace App\Notifications;

use App\Models\User;
use Mathrix\Lumen\Bases\BaseNotification;

/**
 * Class NewSubscriptionNotification.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class NewSubscriptionNotification extends BaseNotification
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


    /**
     * @return array
     */
    public function via(): array
    {
        return [];
    }
}
