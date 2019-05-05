<?php

namespace App\Registrars;

use App\Controllers\GradesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class GradesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class GradesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = GradesController::class;

        $this->rest("grades", $controller, [
            "index" => null,
            "get" => null,
            "post" => "grades:write",
            "patch" => "grades:write",
            "delete" => "grades:delete"
        ]);

        $this->get("grades/slug/{gradeSlug:[a-z0-9\-]+}", "$controller@getBySlug");

        $this->get("grades/by-{what:country}/{id:[1-9]\d*}[/{page:\d+}/{perPage:[1-9]\d*}]", "$controller@by");
    }
}
