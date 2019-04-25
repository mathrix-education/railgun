<?php

namespace App\Registrars;

use App\Controllers\ModulesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ModulesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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
            "index" => "moderator",
            "get" => null,
            "post" => "moderator",
            "patch" => "moderator",
            "delete" => "moderator"
        ]);

        $this->get("modules/by-{what:activity}/{id:[1-9]\d*}/{page:\d+}/{perPage:[1-9]\d*}",
            "$controller@by");

        $this->patch("modules/{moduleId:[1-9]\d*}/mark", [
            "uses" => "$controller@mark",
            "middleware" => "scope:registered"
        ]);
    }
}
