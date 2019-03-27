<?php

namespace App\Registrars;

use App\Controllers\CountriesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class CountriesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
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
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("countries/slug/{countrySlug:[a-z\_]+}", "$controller@getBySlug");
    }
}
