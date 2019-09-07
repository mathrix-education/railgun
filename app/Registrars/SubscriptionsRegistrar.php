<?php

namespace App\Registrars;

use App\Controllers\SubscriptionsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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

        $this->group(["middleware" => "logged"], function () use ($controller) {
            $this->post("subscriptions", "$controller@standardPost");
            $this->post("subscriptions/{subscriptionId}/migrate", "$controller@migrate");
        });
    }
}
