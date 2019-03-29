<?php

namespace App\Listeners;

use App\Events\PasswordForgottenEvent;
use App\Notifications\PasswordForgottenNotification;
use Mathrix\Lumen\Bases\BaseListener;

/**
 * Class PasswordForgottenListener.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class PasswordForgottenListener extends BaseListener
{
    public function handle(PasswordForgottenEvent $event)
    {
        $event->user->notify(new PasswordForgottenNotification($event->user));
    }
}
