<?php

namespace App\Registrars;

use App\Controllers\RecordsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class RecordsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class RecordsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->get("records/{userId:[1-9]\d*}/{page:\d+}/{perPage:\d+}", [
            "middleware" => "scope:admin",
            "uses" => RecordsController::class . "@byUser"
        ]);
    }
}
