<?php

namespace App\Registrars;

use App\Controllers\StatsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class StatsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class StatsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->get("stats/activities", [
            "uses" => StatsController::class . "@activities",
            "middleware" => "scope:registered"
        ]);
    }
}
