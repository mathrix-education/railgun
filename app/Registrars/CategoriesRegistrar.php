<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class CategoriesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $this->registerCRUDRoutes([
            'list' => 'scope:categories:read',
            'read' => 'scope:categories:read',
            'create' => 'scope:categories:write',
            'update' => 'scope:categories:write',
            'delete' => 'scope:categories:delete',
        ]);
    }
}
