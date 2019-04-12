<?php

namespace App\Registrars;

use App\Controllers\BadgesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class BadgesRegistrars.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc2
 */
class BadgesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = BadgesController::class;

        $this->get("badges/coverage", "$controller@coverage");
    }
}
