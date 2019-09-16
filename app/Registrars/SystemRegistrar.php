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
        $this->get('/docs', "$controller@docs");
        $this->get('/system/health[/{count:[1-9]\d*}]', "$controller@health");
        $this->get('/system/sitemap', "$controller@sitemap");
        $this->get('/system/work', "$controller@work");
    }
}
