<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Register routes starting with /purchases.
 */
class PurchasesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index'  => 'scope:purchases:read',
            'std:get'    => 'scope:purchases:read',
            'std:post'   => 'scope:purchases:write',
            'std:patch'  => 'scope:purchases:write',
            'std:delete' => 'scope:purchases:delete',
        ]);
    }
}
