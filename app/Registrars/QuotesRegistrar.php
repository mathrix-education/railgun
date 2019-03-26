<?php

namespace App\Registrars;

use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class QuotesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class QuotesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = QuotesRegistrar::class;

        $this->rest("quotes", $controller, [
            "index" => "admin",
            "get" => "admin",
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("quotes/random/{count:[1-9]\d*}", "$controller@random");
    }
}
