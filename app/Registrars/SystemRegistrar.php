<?php

namespace App\Registrars;

use App\Controllers\SystemController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class SystemRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class SystemRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->get("/", SystemController::class . "@base");
        $this->post("system/sitemaps", SystemController::class . "@sitemaps");
    }
}
