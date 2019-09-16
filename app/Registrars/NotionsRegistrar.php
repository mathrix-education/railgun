<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class NotionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            'std:index' => 'scope:notions:read',
            'std:get' => null,
            'std:post' => 'scope:notions:write',
            'std:patch' => 'scope:notions:write',
            'std:delete' => 'scope:notions:delete',
            'rel:get:activities' => null,
        ]);
    }
}
