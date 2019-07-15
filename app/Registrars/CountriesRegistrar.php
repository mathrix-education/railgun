<?php

namespace App\Registrars;

use App\Controllers\CountriesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class CountriesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class CountriesRegistrar extends BaseRegistrar
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
            "std:post" => "scope:countries:write",
            "std:patch" => "scope:countries:write",
            "std:delete" => "scope:countries:delete",
            "rel:get:grades" => null
        ]);
    }
}
