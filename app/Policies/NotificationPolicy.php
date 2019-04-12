<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Class NotificationPolicy.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.2.0
 */
class NotificationPolicy extends BasePolicy
{
    /**
     * Check if user can mark a notification
     *
     * @param User $currentUser
     * @param DatabaseNotification $notification
     *
     * @return bool
     */
    public function mark(User $currentUser, DatabaseNotification $notification)
    {
        return $currentUser->tokenCan("admin") || $notification->notifiable->id === $currentUser->id;
    }
}
