<?php

namespace App\Registrars;

use App\Controllers\ScopesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class ScopesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.0
 */
class ScopesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->get("scopes", ScopesController::class . "@all");
    }
}
