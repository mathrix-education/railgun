<?php

namespace App\Registrars;

use App\Controllers\ChainerController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ChainerRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ChainerRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->post("chain", ChainerController::class . "@chain");
    }
}
