<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Class BaseNotification
 * Abstract class for notifications
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Notifications
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
abstract class BaseNotification extends Notification
{
    public abstract function via(): array;
}
