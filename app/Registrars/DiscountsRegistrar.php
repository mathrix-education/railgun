<?php

namespace App\Registrars;

use App\Controllers\DiscountsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class DiscountsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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
            "index" => "discounts:read",
            "get" => "discounts:read",
            "post" => "discounts:write",
            "patch" => "discounts:write",
            "delete" => "discounts:delete"
        ]);

        $this->get("discounts/{discountId:[1-9]\d*}/products/{page:\d+}/{perPage:[1-9]\d*}", [
            "uses" => "$controller@getProducts",
            "middleware" => "discounts:read"
        ]);

        $this->patch("discounts/{discountId:[1-9]\d*}/products", [
            "uses" => "$controller@patchProducts",
            "middleware" => "discounts:write"
        ]);

        $this->get("discounts/code/{code:[a-zA-Z0-9\-]+}", "$controller@getByCode");
    }
}
