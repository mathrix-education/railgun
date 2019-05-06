<?php

namespace App\Registrars;

use App\Controllers\SubscriptionsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class SubscriptionsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SubscriptionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = SubscriptionsController::class;

        $this->group(["middleware" => "scope:logged"], function () use ($controller) {
            $this->post("subscriptions", "$controller@create");
            $this->patch("subscriptions/{subscriptionId:[a-z0-9\_]+}", "$controller@update");
        });
        $this->group(["middleware" => "scope:subscriptions:read"], function () use ($controller) {
            $this->get("subscriptions/{subscriptionId:[a-z0-9\_]+}", "$controller@find");
        });
        $this->group(["middleware" => "scope:subscriptions:write"], function () use ($controller) {
            $this->post("subscriptions/{subscriptionId:[a-z0-9\_]+}/cancel", "$controller@cancel");
        });
    }
}
