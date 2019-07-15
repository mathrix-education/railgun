<?php

namespace App\Registrars;

use App\Controllers\CommentsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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
        $this->rest([
            "std:index" => "scope:comments:read",
            "std:get" => "scope:comments:read",
            "std:post" => "scope:logged",
            "std:patch" => "scope:logged",
            "std:delete" => "scope:comments:delete"
        ]);

        $controller = CommentsController::class;

        $this->get("comments/latest/{page:\d+}/{perPage:[1-9]\d*}", [
            "uses" => "$controller@latest",
            "middleware" => "scope:comments:read"
        ]);
    }
}
