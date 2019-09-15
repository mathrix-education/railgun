<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class DiscountsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index' => 'scope:discounts:read',
            'std:get' => 'scope:discounts:read',
            'std:get:code' => 'scope:discounts:read',
            'std:post' => 'scope:discounts:write',
            'std:patch' => 'scope:discounts:write',
            'std:delete' => 'scope:discounts:delete',
            'rel:get:products' => 'scope:discounts:read',
            'rel:patch:products' => 'scope:discounts:write',
        ]);
    }
}
