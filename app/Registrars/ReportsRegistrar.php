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
            "index" => "reports:read",
            "get" => "reports:read",
            "post" => "logged",
            "patch" => "reports:write",
            "delete" => "reports:delete"
        ]);
    }
}
