<?php

namespace App\Registrars;

use App\Controllers\ReportsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ReportsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ReportsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest("reports", ReportsController::class, [
            "index" => "scope:reports:read",
            "get" => "scope:reports:read",
            "post" => "scope:logged",
            "patch" => "scope:reports:write",
            "delete" => "scope:reports:delete"
        ]);
    }
}
