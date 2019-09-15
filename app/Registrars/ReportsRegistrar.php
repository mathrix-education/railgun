<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ReportsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index' => 'scope:reports:read',
            'std:get' => 'scope:reports:read',
            'std:post' => 'logged',
            'std:patch' => 'scope:reports:write',
            'std:delete' => 'scope:reports:delete',
        ]);
    }
}
