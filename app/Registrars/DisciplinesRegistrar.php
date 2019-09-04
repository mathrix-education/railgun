<?php

namespace App\Registrars;

use App\Controllers\DisciplinesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class DisciplinesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class DisciplinesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            "std:index" => null,
            "std:get" => null,
            "std:get:slug" => null,
            "std:post" => "scope:disciplines:write",
            "std:patch" => "scope:disciplines:write",
            "std:delete" => "scope:disciplines:delete"
        ]);
    }
}
