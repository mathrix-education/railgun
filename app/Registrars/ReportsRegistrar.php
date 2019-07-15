<?php

namespace App\Registrars;

use App\Controllers\ReportsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class ReportsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ReportsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            "std:index" => "scope:reports:read",
            "std:get" => "scope:reports:read",
            "std:post" => "scope:logged",
            "std:patch" => "scope:reports:write",
            "std:delete" => "scope:reports:delete"
        ]);
    }
}
