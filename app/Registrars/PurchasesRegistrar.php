<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Register routes starting with /purchases.
 */
class PurchasesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $this->registerCRUDRoutes([
            'list'   => 'scope:purchases:read',
            'read'   => 'scope:purchases:read',
            'create' => 'scope:purchases:write',
            'update' => 'scope:purchases:write',
            'delete' => 'scope:purchases:delete',
        ]);
    }
}
