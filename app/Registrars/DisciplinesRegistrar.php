<?php

namespace App\Registrars;

use App\Controllers\DisciplinesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class DisciplinesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class DisciplinesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest("disciplines", DisciplinesController::class, [
            "index" => null,
            "get" => null,
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);
    }
}
