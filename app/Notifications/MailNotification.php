<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mathrix\Lumen\Bases\BaseNotification;

/**
 * Class MailNotification.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
abstract class MailNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;


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
}
