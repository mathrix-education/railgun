<?php

namespace App\Registrars;

use App\Controllers\CountriesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

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
        $controller = CountriesController::class;

        $this->rest("countries", CountriesController::class, [
            "index" => null,
            "get" => null,
            "post" => "scope:countries:write",
            "patch" => "scope:countries:write",
            "delete" => "scope:countries:delete",
            "related:grades" => null
        ]);

        $this->get("countries/slug/{countrySlug:[a-z\-]+}", "$controller@getBySlug");
    }
}
