<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class PostsRegistrar extends BaseRegistrar
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
            'read' => 'scope:posts:read',
            'create' => 'scope:posts:write',
            'update' => 'scope:posts:write',
            'delete' => 'scope:posts:delete',
        ]);
    }
}
