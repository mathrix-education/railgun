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

        $this->get("products/{productId:[1-9]\d*}/recursive", "$controller@recursive");
        $this->get("products/slug/{productSlug:[a-z0-9\-]+}", "$controller@getBySlug");

        $type = "{type:all|training|section|lesson}";
        $this->get("products/$type/{page:\d+}/{perPage:[1-9]\d+}", "$controller@paginateByType");
        $this->get("products/buyable/$type/{page:\d+}/{perPage:[1-9]\d+}", "$controller@buyable");

        $this->post("products/buy", [
            "uses" => "$controller@buy",
            "middleware" => "scope:registered"
        ]);
    }
}
