<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\WebhooksController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class WebhooksRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = WebhooksController::class;

        $this->post('/webhooks/stripe', "$controller@stripe");
    }
}
