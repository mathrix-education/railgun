<?php

declare(strict_types=1);

namespace App\Registrars;

use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ChaptersRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $this->registerCRUDRoutes([
            'list' => 'scope:chapters:read',
            'read' => null,
            'create' => 'scope:chapters:write',
            'update' => 'scope:chapters:write',
            'delete' => 'scope:chapters:delete',
        ]);
    }
}
