<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class DisciplinesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $this->registerCRUDRoutes([
            'list' => null,
            'read' => null,
            'read:slug' => null,
            'create' => 'scope:disciplines:write',
            'update' => 'scope:disciplines:write',
            'delete' => 'scope:disciplines:delete',
        ]);
    }
}
