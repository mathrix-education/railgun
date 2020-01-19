<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ReportsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $this->registerCRUDRoutes([
            'list'   => 'scope:reports:read',
            'read'   => 'scope:reports:read',
            'create' => 'logged',
            'update' => 'scope:reports:write',
            'delete' => 'scope:reports:delete',
        ]);
    }
}
