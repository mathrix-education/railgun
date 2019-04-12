<?php

namespace App\Registrars;

use App\Controllers\UsersController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class UsersRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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
            "get" => "registered",
            "post" => null,
            "patch" => "registered",
            "delete" => "admin"
        ]);

        $this->group(["middleware" => "scope:registered"], function () use ($controller) {
            $this->post("users/{id:[1-9]\d*}/points", "$controller@points");
            $this->patch("users/{id:[1-9]\d*}/payment-method", "$controller@updatePaymentMethod");
        });

        $this->post("users/search[/{page:\d+}/{perPage:[1-9]\d*}]", "$controller@search");
    }
}
