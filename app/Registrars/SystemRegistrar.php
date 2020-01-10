<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\SystemController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class SystemRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = SystemController::class;

        $this->get('/', "$controller@base");
        $this->get('/env', "$controller@env");
    }
}
