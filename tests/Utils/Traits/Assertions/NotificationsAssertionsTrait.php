<?php
namespace Tests\Utils\Traits\Assertions;

use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * Trait NotificationsTrait
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package Tests\Utils\Traits
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
trait NotificationsAssertionsTrait
{
    /**
     * Assert notification was sent to user
     * @param string $notificationClass
     * @param User $user
     * @param string $channel
     */
    public function assertNotificationSentToVia(string $notificationClass, User $user, string $channel)
    {
        Notification::assertSentTo($user, $notificationClass,
            function ($notification, array $channels) use ($channel) {
                return in_array($channel, $channels);
            }
        );
    }
}
