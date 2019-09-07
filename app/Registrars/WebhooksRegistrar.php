<?php

namespace App\Registrars;

use App\Controllers\WebhooksController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class WebhooksRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class WebhooksRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = WebhooksController::class;

        $this->post("/webhooks/stripe", "$controller@stripe");
    }
}
