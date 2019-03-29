<?php

namespace App\Registrars;

use App\Controllers\ProductsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ProductsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ProductsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ProductsController::class;

        $this->rest("products", $controller, [
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("products/{id:[1-9]\d*}/recursive", "$controller@recursive");
        $this->get("products/buyable[/{type:all|training}]", "$controller@buyable");

        $this->post("products/buy", [
            "uses" => "$controller@buy",
            "middleware" => "scope:registered"
        ]);

        $this->post("products/sell", [
            "uses" => "$controller@sell",
            "middleware" => "scope:support"
        ]);
    }
}
