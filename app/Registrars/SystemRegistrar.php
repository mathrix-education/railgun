<?php

namespace App\Registrars;

use App\Controllers\SystemController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class SystemRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SystemRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->get("/", SystemController::class . "@base");
        $this->get("system/health[/{count:[1-9]\d*}]", SystemController::class . "@health");
        $this->get("system/sitemap", SystemController::class . "@sitemap");
    }
}
