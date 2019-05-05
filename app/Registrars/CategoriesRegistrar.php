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
            "index" => "categories:read",
            "get" => "categories:read",
            "post" => "categories:write",
            "patch" => "categories:delete",
            "delete" => "categories:delete"
        ]);
    }
}
