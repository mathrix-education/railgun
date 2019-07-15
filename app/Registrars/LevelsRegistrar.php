<?php

namespace App\Registrars;

use App\Controllers\LevelsController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class LevelsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class LevelsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $this->rest([
            "std:index" => null
        ]);
    }
}
