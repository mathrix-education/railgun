<?php

namespace App\Registrars;

use App\Controllers\ActivitiesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ActivitiesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ActivitiesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ActivitiesController::class;

        $this->rest("activities", $controller, [
            "index" => "redactor",
            "get" => null,
            "post" => "moderator",
            "patch" => "redactor",
            "delete" => "admin"
        ]);

        $this->get("activities/by-{what:notion}/{id:[1-9]\d*}[/{page:\d+}/{perPage:[1-9]\d*}]",
            "$controller@by");

        $this->group(["middleware" => "scope:registered"], function () use ($controller) {
            $this->patch("activities/{id:[1-9]\d*}/mark", "$controller@mark");
            $this->patch("activities/{id:[1-9]\d*}/unmark", "$controller@unmark");
        });
    }
}
