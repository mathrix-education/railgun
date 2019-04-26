<?php

namespace App\Registrars;

use App\Controllers\ProductsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ProductsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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

        $this->patch("products/{packId:[1-9]\d*}/items", [
            "middleware" => "scope:admin",
            "uses" => "$controller@items"
        ]);

        $this->get("products/{productId:[1-9]\d*}/recursive", "$controller@recursive");
        $this->get("products/slug/{productSlug:[a-z0-9\-]+}", "$controller@getBySlug");

        $type = "{type:all|premium|pack|training|section|lesson}";
        $this->get("products/$type/{page:\d+}/{perPage:[1-9]\d+}", "$controller@paginateByType");
        $this->get("products/buyable/$type/{page:\d+}/{perPage:[1-9]\d+}", "$controller@buyable");

        $this->group(["middleware" => "scope:registered"], function () use ($controller) {
            $this->post("products/cart", "$controller@cart");
            $this->post("products/buy", "$controller@buy");
        });
    }
}
