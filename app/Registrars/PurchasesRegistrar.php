<?php

namespace App\Registrars;

use App\Controllers\PurchasesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class PurchasesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PurchasesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest("purchases", PurchasesController::class, [
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);
    }
}
