<?php

namespace App\Registrars;

use App\Controllers\UsersController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class UsersRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class UsersRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = UsersController::class;

        $this->rest("users", $controller, [
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->post("users/search[/{page:[0-9]+}/{perPage:[1-9]\d*}]", "$controller@search");
    }
}
