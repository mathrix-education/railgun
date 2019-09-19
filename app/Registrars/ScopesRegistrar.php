<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\ScopesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ScopesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->get('scopes', ScopesController::class . '@all');
    }
}
