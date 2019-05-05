<?php

namespace App\Registrars;

use App\Controllers\MarksController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class MarksRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class MarksRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = MarksController::class;

        $this->get("marks/activities/to-revise", [
            "uses" => "$controller@activitiesToRevise",
            "middleware" => "scope:logged"
        ]);
    }
}
