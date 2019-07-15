<?php

namespace App\Registrars;

use App\Controllers\UsersController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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

        $this->rest([
            "std:index" => "scope:users:read",
            "std:get" => "scope:logged",
            "std:post" => null,
            "std:patch" => "scope:logged",
            "std:delete" => "scope:users:delete",
            "rel:get:records" => "scope:records:read",
            "rel:get:subscriptions" => "scope:logged",
            "rel:get:transactions" => "scope:logged"
        ]);

        $this->group(["middleware" => "scope:logged"], function () use ($controller) {
            $this->post("users/{userId:[1-9]\d*}/points", "$controller@points");
        });

        $this->group(["middleware" => "scope:users:read"], function () use ($controller) {
            $this->get("users/search/{query}/{page:\d+}/{perPage:[1-9]\d*}", "$controller@search");
        });

        $this->group(["middleware" => "scope:users:write"], function () use ($controller) {
            $this->delete("users/{userId:[1-9]\d*}/cache", "$controller@cache");
        });
    }
}
