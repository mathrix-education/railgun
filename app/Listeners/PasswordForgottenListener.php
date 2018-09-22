<?php

namespace App\Listeners;

use App\Events\PasswordForgottenEvent;
use App\Notifications\PasswordForgottenNotification;

/**
 * Class PasswordForgottenListener.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class PasswordForgottenListener extends BaseListener
{
    public function handle(PasswordForgottenEvent $event)
    {
        $event->user->notify(new PasswordForgottenNotification($event->user));
    }
}
