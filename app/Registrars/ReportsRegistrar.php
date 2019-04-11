<?php

namespace App\Registrars;

use App\Controllers\ReportsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ReportsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ReportsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest("reports", ReportsController::class, [
            "index" => "admin",
            "get" => "admin",
            "post" => "registered",
            "patch" => "admin",
            "delete" => "admin"
        ]);
    }
}
