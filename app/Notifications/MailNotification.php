<?php

namespace App\Notifications;

/**
 * Class MailNotification.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Notifications
 */
abstract class MailNotification extends BaseNotification
{
    /**
     * @return array
     */
    public function via(): array
    {
        return (env("MAILGUN_ENVIRONMENT") === "disabled" && app()->environment() !== "testing") || app()->environment() === "offline" ? [] : ["mail"];
    }
}
