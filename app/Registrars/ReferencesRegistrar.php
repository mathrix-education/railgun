<?php

namespace App\Registrars;

use App\Controllers\ReferencesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ReferencesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ReferencesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ReferencesController::class;

        $this->rest("references", $controller, [
            "index" => "moderator",
            "get" => "moderator",
            "post" => "moderator",
            "patch" => "moderator",
            "delete" => "moderator"
        ]);

        $this->get("query/{query}[/{page:[0-9]+}/{perPage:[1-9]\d*}]", "$controller@query");
    }
}
