<?php

namespace App\Registrars;

use App\Controllers\NotionsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class NotionsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class NotionsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            "std:index" => "scope:notions:read",
            "std:get" => null,
            "std:post" => "scope:notions:write",
            "std:patch" => "scope:notions:write",
            "std:delete" => "scope:notions:delete",
            "rel:get:activities" => null
        ]);
    }
}
