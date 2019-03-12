<?php

namespace App\Listeners;

use App\Events\PasswordResetEvent;
use App\Notifications\PasswordResetNotification;

/**
 * Class PasswordResetListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Listeners
 */
class PasswordResetListener extends BaseListener
{
    /**
     * @param PasswordResetEvent $event
     */
    public function handle(PasswordResetEvent $event)
    {
        $event->user->notify(new PasswordResetNotification($event->user));
    }
}
