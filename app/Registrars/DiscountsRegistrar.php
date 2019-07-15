<?php

namespace App\Registrars;

use App\Controllers\DiscountsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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
        $this->rest([
            "std:index" => "scope:discounts:read",
            "std:get" => "scope:discounts:read",
            "std:get:code" => "scope:discounts:read",
            "std:post" => "scope:discounts:write",
            "std:patch" => "scope:discounts:write",
            "std:delete" => "scope:discounts:delete",
            "rel:get:products" => "scope:discounts:read",
            "rel:patch:products" => "scope:discounts:write"
        ]);
    }
}
