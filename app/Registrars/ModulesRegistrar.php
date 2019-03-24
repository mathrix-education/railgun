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
        $this->get("modules/by-{what:activity}/{id:[1-9]\d*}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", [
            "uses" => ModulesController::class . "@search",
            "middleware" => "scope:redactor"
        ]);
        $this->post("modules/search", [
            "uses" => ModulesController::class . "@search",
            "middleware" => "scope:redactor"
        ]);
        $this->patch("modules/{moduleId:[1-9]\d*}/mark", [
            "uses" => ModulesController::class . "@mark",
            "middleware" => "scope:registered"
        ]);

        $this->rest("modules", ModulesController::class, [
            "index" => null,
            "get" => null,
            "post" => "moderator",
            "patch" => "redactor",
            "delete" => "admin"
        ]);
    }
}
