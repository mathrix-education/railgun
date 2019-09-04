<?php

namespace App\Registrars;

use App\Controllers\PlansController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class PlansRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class PlansRegistrar extends BaseRegistrar
{
    public function register(): void
    {
        $controller = PlansController::class;
        $this->get("/plans", "$controller@all");
    }
}
