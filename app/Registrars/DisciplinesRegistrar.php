<?php

namespace App\Registrars;

use App\Controllers\DisciplinesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class DisciplinesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DisciplinesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = DisciplinesController::class;

        $this->rest("disciplines", $controller, [
            "index" => null,
            "get" => null,
            "post" => "scope:disciplines:write",
            "patch" => "scope:disciplines:write",
            "delete" => "scope:disciplines:delete"
        ]);

        $this->get("disciplines/slug/{disciplineSlug:[a-z\-]+}", "$controller@getBySlug");
    }
}
