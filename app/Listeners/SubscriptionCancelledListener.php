<?php

namespace App\Listeners;

use App\Events\SubscriptionCancelledEvent;
use App\Models\User;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class SubscriptionCancelledListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.2-rc1
 */
class SubscriptionCancelledListener extends BaseListener
{
    /**
     * Handle PasswordResetEvent.
     *
     * @param SubscriptionCancelledEvent $event
     */
    public function handle(SubscriptionCancelledEvent $event)
    {
        /** @var User $user */
        $user = User::query()
            ->where("braintree_id", "=", $event->customer->id)
            ->firstOrFail();
        $user->invalidate();
    }
}
