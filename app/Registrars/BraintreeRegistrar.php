<?php

namespace App\Registrars;

use App\Controllers\BraintreeController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class BraintreeRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class BraintreeRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = BraintreeController::class;

        $this->post("braintree/webhook", "$controller@webhook");

        $this->get("braintree/client-token", [
            "uses" => "$controller@clientToken",
            "middleware" => "scope:registered"
        ]);
    }
}
