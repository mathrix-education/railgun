<?php

namespace App\Notifications;

use Mathrix\Lumen\Notifications\BaseNotification;

/**
 * Class MailNotification.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
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
