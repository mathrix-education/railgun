<?php

namespace App\Registrars;

use App\Controllers\CategoriesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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
        $this->rest([
            "std:index" => "scope:categories:read",
            "std:get" => "scope:categories:read",
            "std:post" => "scope:categories:write",
            "std:patch" => "scope:categories:write",
            "std:delete" => "scope:categories:delete",
            "rel:get:posts" => "scope:posts:read"
        ]);
    }
}
