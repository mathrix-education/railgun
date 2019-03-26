<?php

namespace App\Registrars;

use App\Controllers\LevelsController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class LevelsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class LevelsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->get("levels[/{page:[0-9]+}/{perPage:[1-9]\d*}]", LevelsController::class . "@index");
    }
}
