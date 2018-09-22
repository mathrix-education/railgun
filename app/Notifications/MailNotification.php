<?php
namespace App\Notifications;

/**
 * Class MailNotification
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package   App\Notifications
 * @since
 * @copyright Mathrix Education SA
 */
abstract class MailNotification extends BaseNotification
{
    /**
     * @return array
     */
    public function via(): array
    {
        return env("MAILGUN_ENVIRONMENT") === "disabled" || app()->environment() === "offline" ? [] : ["mail"];
    }
}
