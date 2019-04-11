<?php

namespace App\Listeners;

use App\Events\PasswordResetEvent;
use App\Notifications\PasswordResetNotification;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class PasswordResetListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
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
