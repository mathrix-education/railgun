<?php

namespace App\Registrars;

use App\Controllers\CategoriesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class CategoriesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class CategoriesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest("categories", CategoriesController::class, [
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);
    }
}
