<?php

namespace App\Registrars;

use App\Controllers\NotificationsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class NotificationsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class NotificationsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = NotificationsController::class;

        $this->group(["prefix" => "notifications", "middleware" => "scope:registered"], function () use ($controller) {
            $this->patch("all/mark/read", "$controller@markAllRead");
            $this->get("all/{page}/{perPage}", "$controller@all");
            $this->get("unread/{page}/{perPage}", "$controller@unread");
            $this->get("read/{page}/{perPage}", "$controller@read");
            $this->patch("{notificationId}/mark/{type}", "$controller@mark");
        });
    }
}
