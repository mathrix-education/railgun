<?php

namespace App\Registrars;

use App\Controllers\CommentsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class CommentsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class CommentsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = CommentsController::class;

        $this->rest("comments", $controller, [
            "index" => "scope:comments:read",
            "get" => "scope:comments:read",
            "post" => "scope:logged",
            "patch" => "scope:logged",
            "delete" => "scope:comments:delete"
        ]);

        $this->get("comments/latest/{page:\d+}/{perPage:[1-9]\d*}", [
            "uses" => "$controller@latest",
            "middleware" => "scope:comments:read"
        ]);
    }
}
