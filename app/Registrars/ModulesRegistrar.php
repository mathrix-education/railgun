<?php

namespace App\Registrars;

use App\Controllers\ModulesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ModulesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ModulesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ModulesController::class;

        $this->rest("modules", $controller, [
            "index" => null,
            "get" => null,
            "post" => "moderator",
            "patch" => "redactor",
            "delete" => "admin"
        ]);

        $this->get("modules/by-{what:activity}/{id:[1-9]\d*}[/{page:[0-9]+}/{perPage:[1-9]\d*}]",
            "$controller@by");

        $this->patch("modules/{moduleId:[1-9]\d*}/mark", [
            "uses" => "$controller@mark",
            "middleware" => "scope:registered"
        ]);

        $this->post("modules/search", [
            "uses" => "$controller@search",
            "middleware" => "scope:redactor"
        ]);
    }
}
