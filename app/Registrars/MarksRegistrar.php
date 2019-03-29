<?php

namespace App\Registrars;

use App\Controllers\MarksController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class MarksRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class MarksRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = MarksController::class;

        $this->group(["middleware" => "scope:registered"], function () use ($controller) {
            $this->get("marks/activities/to-revise", "$controller@activitiesToRevise");
            $this->get("marks/activities/view", "$controller@activitiesView");
        });
    }
}
