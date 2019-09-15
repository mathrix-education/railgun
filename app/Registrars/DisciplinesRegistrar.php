<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class DisciplinesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index' => null,
            'std:get' => null,
            'std:get:slug' => null,
            'std:post' => 'scope:disciplines:write',
            'std:patch' => 'scope:disciplines:write',
            'std:delete' => 'scope:disciplines:delete',
        ]);
    }
}
