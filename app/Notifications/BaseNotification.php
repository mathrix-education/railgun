<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Class BaseNotification.
 * Abstract class for notifications.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Notifications
 */
abstract class BaseNotification extends Notification
{
    abstract public function via(): array;
}
