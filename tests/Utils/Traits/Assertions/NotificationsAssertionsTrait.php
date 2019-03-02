<?php
namespace Tests\Utils\Traits\Assertions;

use App\Models\User;
use Illuminate\Support\Facades\Notification;

/**
 * Trait NotificationsAssertionsTrait.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits\Assertions
 */
trait NotificationsAssertionsTrait
{
    /**
     * Assert notification was sent to user.
     *
     * @param string $notificationClass
     * @param User   $user
     * @param string $channel
     */
    public function assertNotificationSentToVia(string $notificationClass, User $user, string $channel)
    {
        Notification::assertSentTo($user, $notificationClass,
            function ($notification, array $channels) use ($channel) {
                return \in_array($channel, $channels, true);
            }
        );
    }
}
