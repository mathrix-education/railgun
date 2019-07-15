<?php

namespace App\Registrars;

use App\Controllers\ModulesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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

        $this->rest([
            "std:index" => "scope:modules:read",
            "std:get" => null,
            "std:post" => "scope:modules:write",
            "std:patch" => "scope:modules:write",
            "std:delete" => "scope:modules:delete",
            "rel:get:comments" => null,
            "rel:get:reports" => "scope:reports:read"
        ]);

        $controller = ModulesController::class;

        $this->get("modules/{moduleId:[1-9]\d*}/chapter/recursive", "$controller@chapterRecursive");

        $this->patch("modules/{moduleId:[1-9]\d*}/mark", [
            "uses" => "$controller@mark",
            "middleware" => "scope:logged"
        ]);
    }
}
