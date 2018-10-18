<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Class BaseNotification
 * Abstract class for notifications.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
abstract class BaseNotification extends Notification
{
    abstract public function via(): array;
}
