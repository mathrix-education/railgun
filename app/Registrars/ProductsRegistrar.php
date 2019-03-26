<?php

namespace App\Registrars;

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
        $controller = ProductsRegistrar::class;

        $this->rest("products", $controller, [
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("products/{id:[1-9]\d*}/recursive", "$controller@recursive");
        $this->get("products/buyable[/{type:all}]", "$controller@recursive");

        $this->group(["middleware" => "scope:support"], function () {
            $this->post("products/buy", "ProductController@buy");
            $this->post("products/sale", "ProductController@sale");
        });
    }
}
