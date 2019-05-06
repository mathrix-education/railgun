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
            "index" => "scope:discounts:read",
            "get" => "scope:discounts:read",
            "post" => "scope:discounts:write",
            "patch" => "scope:discounts:write",
            "delete" => "scope:discounts:delete",
            "related:products" => "scope:discounts:read"
        ]);

        $this->patch("discounts/{discountId:[1-9]\d*}/products", [
            "uses" => "$controller@patchProducts",
            "middleware" => "scope:discounts:write"
        ]);

        $this->get("discounts/code/{code:[a-zA-Z0-9\-]+}", "$controller@getByCode");
    }
}
