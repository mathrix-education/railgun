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
            "index" => "scope:modules:read",
            "get" => null,
            "post" => "scope:modules:write",
            "patch" => "scope:modules:write",
            "delete" => "scope:modules:delete",
            "related:comments" => null,
            "related:reports" => "scope:reports:read"
        ]);

        $this->get("modules/{moduleId:[1-9]\d*}/chapter/recursive", "$controller@chapterRecursive");

        $this->patch("modules/{moduleId:[1-9]\d*}/mark", [
            "uses" => "$controller@mark",
            "middleware" => "scope:logged"
        ]);

    }
}
