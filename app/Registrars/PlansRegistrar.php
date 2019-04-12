<?php

namespace App\Registrars;

use App\Controllers\PlansController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class PlansRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class PlansRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = PlansController::class;

        $this->get("plans", "$controller@all");
        $this->get("plans/{planId:[a-z0-9\_]+}", "$controller@find");
    }
}
