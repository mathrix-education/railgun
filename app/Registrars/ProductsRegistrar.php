<?php

namespace App\Registrars;

use App\Controllers\ProductsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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

        $this->rest([
            "std:index" => "scope:products:read",
            "std:get" => "scope:products:read",
            "std:get:slug" => null,
            "std:post" => "scope:products:write",
            "std:patch" => "scope:products:write",
            "std:delete" => "scope:products:delete",
            "rel:get:items" => null,
            "rel:patch:items" => "scope:products:write"
        ]);

        $this->get("products/{productId:[1-9]\d*}/recursive", "$controller@recursive");

        $type = "{type:all|premium|pack|training|section|lesson}";
        $this->get("products/$type", "$controller@paginateByType");
        $this->get("products/buyable/$type", "$controller@buyable");
        $this->post("products/cart", "$controller@cart");

        $this->group(["middleware" => "scope:logged"], function () use ($controller) {
            $this->post("products/buy", "$controller@buy");
        });
    }
}
