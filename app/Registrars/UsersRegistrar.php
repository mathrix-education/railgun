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
            "std:get" => "logged",
            "std:post" => null,
            "std:patch" => "logged",
            "std:delete" => "scope:users:delete",
            "rel:get:grade" => null,
            "rel:get:transactions" => "logged"
        ]);

        $this->group(["middleware" => "logged"], function () use ($controller) {
            $this->get("/users/{userId}/cards", "$controller@getCards");
            $this->get("/users/{userId}/subscriptions", "$controller@getSubscriptions");
        });

        $this->group(["middleware" => "scope:users:read"], function () use ($controller) {
            $this->get("users/search/{query}", "$controller@search");
        });

        $this->group(["middleware" => "scope:users:write"], function () use ($controller) {
            $this->delete("users/{userId}/cache", "$controller@cache");
        });
    }
}
