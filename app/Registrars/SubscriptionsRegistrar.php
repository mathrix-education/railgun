<?php

namespace App\Registrars;

use App\Controllers\SubscriptionsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class SubscriptionsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SubscriptionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = SubscriptionsController::class;

        $this->group(["middleware" => "scope:registered"], function () use ($controller) {
            $this->post("", "$controller@post");
            $this->get("{subscriptionId:[a-z0-9\_]+}", "$controller@get");
            $this->post("{subscriptionId:[a-z0-9\_]+}/cancel", "$controller@cancel");
            $this->get("by-user/{userId:[1-9]\d*}", "$controller@byUser");
        });
    }
}
