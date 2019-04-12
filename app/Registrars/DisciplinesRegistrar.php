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
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("disciplines/slug/{disciplineSlug:[a-z\-]+}", "$controller@getBySlug");
        $this->get("disciplines/by-grade/{gradeId:[1-9]\d*}/{page:\d+}/{perPage:[1-9]\d*}", "$controller@byGrade");
    }
}
