<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class CategoriesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index' => 'scope:categories:read',
            'std:get' => 'scope:categories:read',
            'std:post' => 'scope:categories:write',
            'std:patch' => 'scope:categories:write',
            'std:delete' => 'scope:categories:delete',
            'rel:get:posts' => 'scope:posts:read',
        ]);
    }
}
