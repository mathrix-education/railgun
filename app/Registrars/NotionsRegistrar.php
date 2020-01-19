<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class NotionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $this->registerCRUDRoutes([
            'list' => 'scope:notions:read',
            'read' => null,
            'create' => 'scope:notions:write',
            'update' => 'scope:notions:write',
            'delete' => 'scope:notions:delete',
        ]);
    }
}
