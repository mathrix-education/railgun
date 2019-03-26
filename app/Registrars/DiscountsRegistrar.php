<?php

namespace App\Registrars;

use App\Controllers\DiscountsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class DiscountsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class DiscountsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = DiscountsController::class;

        $this->rest("discounts", $controller, [
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->post("discounts/check", [
            "uses" => "$controller@check",
            "middleware" => "scope:registered"
        ]);
    }
}
