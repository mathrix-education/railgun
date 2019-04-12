<?php

namespace App\Registrars;

use App\Controllers\SettingsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class SettingsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SettingsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = SettingsController::class;

        $this->get("settings", "$controller@all");
        $this->get("settings/{key:[a-z0-9\-\_]+}", "$controller@read");

        $this->group(["middleware" => "scope:admin"], function () use ($controller) {
            $this->patch("settings/{key:[a-z0-9\-\_]+}", "$controller@write");
            $this->delete("settings/{key:[a-z0-9\-\_]+}", "$controller@forgot");
        });
    }
}
