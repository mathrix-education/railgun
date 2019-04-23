<?php

namespace App\Notifications;

use App\Models\User;
use Braintree\Subscription;
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
    /** @var Subscription $subscription */
    public $subscription;


    /**
     * NewSubscriptionEvent constructor.
     *
     * @param User $user
     * @param Subscription $subscription
     */
    public function __construct(User $user, Subscription $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
    }


    /**
     * @return array
     */
    public function via(): array
    {
        return []; // Currently no channel is configured to handle this event.
    }
}
