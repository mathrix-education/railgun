<?php

namespace App\Registrars;

use App\Controllers\NotionsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class NotionsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class NotionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = NotionsController::class;

        $this->rest("notions", $controller, [
            "index" => "admin",
            "get" => null,
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("notions/by-{what:chapter}/{id:[1-9]\d*}/{page:\d+}/{perPage:[1-9]\d*}",
            "$controller@by");
    }
}
