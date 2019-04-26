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
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->group(["middleware" => "scope:admin"], function () use ($controller) {
            $this->get("discounts/{discountId:[1-9]\d*}/products/{page:\d+}/{perPage:[1-9]\d*}",
                "$controller@getProducts");
            $this->patch("discounts/{discountId:[1-9]\d*}/products/{page:\d+}/{perPage:[1-9]\d*}",
                "$controller@patchProducts");
        });

        $this->get("discounts/code/{code:[a-zA-Z0-9\-]+}", "$controller@getByCode");
    }
}
