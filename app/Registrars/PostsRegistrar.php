<?php

namespace App\Registrars;

use App\Controllers\PostsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class PostsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
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
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("posts/by-{what:category}/{id:[1-9]\d*}/{page:\d+}/{perPage:[1-9]\d*}",
            "$controller@by");

        $this->get("posts/latest/{page:\d+}/{perPage:[1-9]\d*}", "$controller@latest");
    }
}
