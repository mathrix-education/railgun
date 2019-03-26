<?php

namespace App\Registrars;

use App\Controllers\ProfileController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ProfileRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ProfileRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ProfileController::class;

        $this->post("profile/exists", "$controller@exists");

        $this->group(["middleware" => "scope:registered"], function () use ($controller) {
            $this->get("profile", "$controller@get");
            $this->patch("profile", "$controller@patch");
            $this->post("profile/points", "$controller@points");
            $this->patch("profile/payment-method", "$controller@updatePaymentMethod");
        });
    }
}
