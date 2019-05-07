<?php

namespace App\Registrars;

use App\Controllers\NotionsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class NotionsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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
            "index" => "scope:notions:read",
            "get" => null,
            "post" => "scope:notions:write",
            "patch" => "scope:notions:write",
            "delete" => "scope:notions:delete",
            "related:activities" => null
        ]);
    }
}
