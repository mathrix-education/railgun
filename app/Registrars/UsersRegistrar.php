<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class UsersRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $this->registerCRUDRoutes([
            'list'   => 'scope:users:read',
            'read'   => 'logged',
            'create' => null,
            'update' => 'logged',
            'delete' => 'scope:users:delete',
        ]);
    }
}
