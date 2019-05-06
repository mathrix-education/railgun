<?php

namespace App\Registrars;

use App\Controllers\PostsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

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
        $controller = PostsController::class;

        $this->rest("posts", $controller, [
            "index" => null,
            "get" => "scope:posts:read",
            "post" => "scope:posts:write",
            "patch" => "scope:posts:write",
            "delete" => "scope:posts:delete"
        ]);

        $this->get("posts/latest/{page:\d+}/{perPage:[1-9]\d*}", "$controller@latest");
    }
}
