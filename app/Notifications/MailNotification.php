<?php

namespace App\Notifications;

use Mathrix\Lumen\Bases\BaseNotification;

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
        if (env("APP_ENV") === "dev") {
            return [];
        }

        return ["mail"];
    }
