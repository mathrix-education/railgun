<?php

namespace App\Registrars;

use App\Controllers\PostsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class PostsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class PostsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            "std:index" => null,
            "std:get" => "scope:posts:read",
            "std:post" => "scope:posts:write",
            "std:patch" => "scope:posts:write",
            "std:delete" => "scope:posts:delete"
        ]);

        $controller = PostsController::class;

        $this->get("posts/latest", "$controller@latest");
    }
}
