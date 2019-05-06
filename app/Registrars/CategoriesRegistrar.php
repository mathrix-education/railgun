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
        $controller = CategoriesController::class;

        $this->rest("categories", $controller, [
            "index" => "scope:categories:read",
            "get" => "scope:categories:read",
            "post" => "scope:categories:write",
            "patch" => "scope:categories:write",
            "delete" => "scope:categories:delete",
            "related:posts" => "scope:posts:read"
        ]);
    }
}
