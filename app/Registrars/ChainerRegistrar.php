<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\ChainerController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class ChainerRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register() : void
    {
        $this->post('chain', ChainerController::class . '@chain');
    }
}
